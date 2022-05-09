<?php

declare(strict_types=1);

namespace Drupal\se_xero\Service;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\se_customer\Entity\Customer;
use Drupal\xero\Plugin\DataType\XeroItemList;
use Drupal\xero\XeroQuery;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Service to update Xero when a contact is saved.
 */
class XeroContactService {

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * A Xero query.
   *
   * @var \Drupal\xero\XeroQuery
   */
  protected $xeroQuery;

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * SeXeroContactService constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger for logging.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typed_data_manager
   *   Used for loading data.
   * @param \Drupal\xero\XeroQuery $xero_query
   *   Provide ability to query Xero.
   */
  public function __construct(LoggerInterface $logger, TypedDataManagerInterface $typed_data_manager, XeroQuery $xero_query) {
    $this->logger = $logger;
    $this->typedDataManager = $typed_data_manager;
    $this->xeroQuery = $xero_query;
  }

  /**
   * Lookup a contact in Xero.
   *
   * @param \Drupal\se_customer\Entity\Customer $customer
   *   The customer to check for.
   *
   * @return bool|\Drupal\Core\TypedData\TypedDataInterface|null
   *   The contact if they already exist in Xero.
   */
  public function lookupContact(Customer $customer) {
    // Check if its an existing contact and update fields vs new.
    if (isset($customer->se_xero_uuid->value) && $contact = $this->lookupByContactId($customer->se_xero_uuid->value)) {
      return $contact;
    }

    if (isset($customer->se_bu_id->value) && $contact = $this->lookupByContactNumber($customer->se_bu_id->value)) {
      return $contact;
    }

    if (isset($customer->se_bu_email->value) && $contact = $this->lookupByContactEmailAddress($customer->se_bu_email->value)) {
      return $contact;
    }

    return FALSE;
  }

  /**
   * Helper function to retrieve contacts by number from Xero.
   *
   * @param int $contact_number
   *   The contact number to lookup.
   *
   * @return bool|\Drupal\Core\TypedData\TypedDataInterface|null
   *   The contact if they exist.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function lookupByContactNumber(int $contact_number) {
    if ($contact_number === NULL) {
      return FALSE;
    }
    $this->xeroQuery->setType('xero_contact');
    $this->xeroQuery->addCondition('ContactNumber', $contact_number);
    $item_list = $this->xeroQuery->execute();

    $result = FALSE;
    if ($item_list !== FALSE && count($item_list) !== 0) {
      $result = $item_list->get(0);
    }
    return $result;
  }

  /**
   * Helper function to retrieve contact by id from Xero.
   *
   * @param string $contact_id
   *   The contact id to lookup.
   *
   * @return bool|\Drupal\Core\TypedData\TypedDataInterface|null
   *   The contact if they exist.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function lookupByContactId(string $contact_id) {
    if ($contact_id === NULL) {
      return FALSE;
    }
    $this->xeroQuery->setType('xero_contact');
    $this->xeroQuery->addCondition('ContactID', $contact_id, 'guid');
    $item_list = $this->xeroQuery->execute();

    $result = FALSE;
    if ($item_list !== FALSE && count($item_list) !== 0) {
      $result = $item_list->get(0);
    }
    return $result;
  }

  /**
   * Helper function to retrieve contact by email address from Xero.
   *
   * @param string $email
   *   The email to lookup.
   *
   * @return bool|\Drupal\Core\TypedData\TypedDataInterface|null
   *   The contact if they exist.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function lookupByContactEmailAddress(string $email) {
    if (empty($email)) {
      return FALSE;
    }
    $this->xeroQuery->setType('xero_contact');
    $this->xeroQuery->addCondition('EmailAddress', $email);
    $item_list = $this->xeroQuery->execute();

    $result = FALSE;
    if ($item_list !== FALSE && count($item_list) !== 0) {
      $result = $item_list->get(0);
    }
    return $result;
  }

  /**
   * Create an array of data to sync to xero.
   *
   * @param \Drupal\Core\Config\ImmutableConfig $settings
   *   Provide config access.
   * @param \Drupal\xero\Plugin\DataType\XeroItemList $contacts
   *   The Item list.
   * @param \Drupal\se_customer\Entity\Customer $customer
   *   The Item list.
   *
   * @return \Drupal\xero\Plugin\DataType\XeroItemList
   *   The item list ready to post to Xero.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  private function setContactValues(ImmutableConfig $settings, XeroItemList $contacts, Customer $customer) {
    $values = [
      'ContactNumber' => $customer->id(),
      'Name' => $customer->getName(),
      'EmailAddress' => $customer->se_email->value,
      'Phones' => [],
      'IsBusiness' => TRUE,
      'DefaultCurrency' => $settings->get('system.currency'),
    ];

    $contacts->appendItem($values);

    // @todo Make nicer.
    $name = str_replace(['-', '  '], ['', ' '], $customer->getName());
    $names = explode(' ', $name);
    $mainContacts = \Drupal::service('se_contact.service')->loadMainContactsByCustomer($customer);
    $storage = \Drupal::entityTypeManager()->getStorage('se_contact');

    /** @var \Drupal\se_contact\Entity\Contact $contact */
    foreach ($storage->loadMultiple($mainContacts) as $contact) {
      if (isset($contact->se_phone->value)) {
        $contacts->get(0)->get('Phones')->appendItem([
          'PhoneType' => 'DEFAULT',
          'PhoneNumber' => $contact->se_phone->value,
        ]);
      }
      $names = explode(' ', $contact->getName());
    }
    $contacts->get(0)->get('FirstName')->setValue(array_shift($names));
    $contacts->get(0)->get('LastName')->setValue(implode(' ', $names));

    return $contacts;
  }

  /**
   * Create a Customer/Contact in Xero.
   *
   * @param \Drupal\se_customer\Entity\Customer $customer
   *   Customer to process.
   *
   * @return bool
   *   The upload transaction result.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function sync(Customer $customer) {
    $settings = \Drupal::configFactory()->get('se_xero.settings');
    if (!$settings->get('system.enabled')) {
      return FALSE;
    }

    // Setup the data structure.
    $list_definition = $this->typedDataManager->createListDataDefinition('xero_contact');
    /** @var \Drupal\xero\Plugin\DataType\XeroItemList $contacts */
    $contacts = $this->typedDataManager->create($list_definition, []);

    // Setup the values.
    $contacts = $this->setContactValues($settings, $contacts, $customer);

    // Check if it is an existing contact that is to be updated vs new contact.
    if ($contact = $this->lookupContact($customer)) {
      // Set the ContactID so it the values we're sending act as updates.
      $xeroQuery->setId($contact->get('ContactID')->getValue());
    }

    $this->xeroQuery->setType('xero_contact')
      ->setData($contacts)
      ->setMethod('post');

    $result = $this->xeroQuery->execute();

    if ($result === FALSE) {
      $this->logger->log(LogLevel::ERROR, (string) new FormattableMarkup('Cannot create contact @customer, operation failed.', [
        '@customer' => $customer->getName(),
      ]));
      return FALSE;
    }

    if ($result->count() > 0) {
      /** @var \Drupal\xero\Plugin\DataType\Contact $createdXeroContact */
      $createdXeroContact = $result->get(0);
      $remote_id = $createdXeroContact->get('ContactID')->getValue();
      $customer->set('se_xero_uuid', $remote_id);
      $customer->xero_syncing = TRUE;
      $customer->save();

      $this->logger->log(LogLevel::INFO, (string) new FormattableMarkup('Created contact @customer with remote id @remote_id.', [
        '@customer' => $customer->getName(),
        '@remote_id' => $remote_id,
      ]));
      return TRUE;
    }
    return FALSE;
  }

}
