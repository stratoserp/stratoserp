<?php

namespace Drupal\se_xero\Service;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\xero\Plugin\DataType\XeroItemList;
use Drupal\xero\XeroQueryFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class XeroContactService {

  /**
   * A logger instance.
   *
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * A Xero query.
   *
   * @var XeroQueryFactory;
   */
  protected $xeroQueryFactory;

  /**
   * The typed data manager.
   *
   * @var TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * SeXeroContactService constructor.
   *
   * @param LoggerInterface $logger
   * @param TypedDataManagerInterface $typed_data_manager
   * @param XeroQueryFactory $xero_query_factory
   */
  public function __construct(LoggerInterface $logger, TypedDataManagerInterface $typed_data_manager, XeroQueryFactory $xero_query_factory) {
    $this->logger = $logger;
    $this->typedDataManager = $typed_data_manager;
    $this->xeroQueryFactory = $xero_query_factory;
  }

  /**
   * @param \Drupal\node\Entity\Node $node
   *
   * @return bool|\Drupal\Core\TypedData\TypedDataInterface|null
   */
  public function lookupContact(Node $node) {
    // Check if its an existing contact and update fields vs new.
    if (isset($node->se_xero_uuid->value) && $contact = $this->lookupByContactID($node->se_xero_uuid->value)) {
      return $contact;
    }

    if (isset($node->field_cu_id->value) && $contact = $this->lookupByContactNumber($node->field_cu_id->value)) {
      return $contact;
    }

    if (isset($node->field_cu_email->value) && $contact = $this->lookupByContactEmailAddress($node->field_cu_email->value)) {
      return $contact;
    }

    return FALSE;
  }

  /**
   * @param int $contact_number
   *
   * @return bool|\Drupal\Core\TypedData\TypedDataInterface|null
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function lookupByContactNumber(int $contact_number) {
    if ($contact_number === NULL) {
      return FALSE;
    }
    $xeroQuery = $this->xeroQueryFactory->get();
    $xeroQuery->setType('xero_contact');
    $xeroQuery->addCondition('ContactNumber', $contact_number);
    $item_list = $xeroQuery->execute();

    $result = FALSE;
    if ($item_list !== FALSE && count($item_list) !== 0) {
      $result = $item_list->get(0);
    }
    return $result;
  }

  /**
   * @param string $contact_id
   *
   * @return bool|\Drupal\Core\TypedData\TypedDataInterface|null
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function lookupByContactID(string $contact_id) {
    if ($contact_id === NULL) {
      return FALSE;
    }
    $xeroQuery = $this->xeroQueryFactory->get();
    $xeroQuery->setType('xero_contact');
    $xeroQuery->addCondition('ContactID', $contact_id, 'guid');
    $item_list = $xeroQuery->execute();

    $result = FALSE;
    if ($item_list !== FALSE && count($item_list) !== 0) {
      $result = $item_list->get(0);
    }
    return $result;
  }


  /**
   * @param string $email
   *
   * @return bool|\Drupal\Core\TypedData\TypedDataInterface|null
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function lookupByContactEmailAddress(string $email) {
    if (empty($email)) {
      return FALSE;
    }
    $xeroQuery = $this->xeroQueryFactory->get();
    $xeroQuery->setType('xero_contact');
    $xeroQuery->addCondition('EmailAddress', $email);
    $item_list = $xeroQuery->execute();

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
   * @param \Drupal\xero\Plugin\DataType\XeroItemList $contacts
   * @param \Drupal\node\Entity\Node $node
   *
   * @return \Drupal\xero\Plugin\DataType\XeroItemList
   */
  private function setContactValues(ImmutableConfig $settings, XeroItemList $contacts, Node $node) {
    $values = [
      'ContactNumber' => $node->field_cu_id->value,
      'Name' => $node->title->value,
      'EmailAddress' => $node->field_cu_email->value,
      'Phones' => [],
      'IsCustomer' => TRUE,
      'DefaultCurrency' => $settings->get('system.currency'),
    ];

    $contacts->appendItem($values);

    // TODO Make nicer
    $name = str_replace(['-', '  '], ['', ' '], $node->title->value);
    $names = explode(' ', $name);
    if ($main_contact = \Drupal::service('se_contact.service')->loadMainContactByCustomer($node)) {
      if (isset($main_contact->field_cu_phone->value)) {
        $contacts->get(0)->get('Phones')->appendItem([
          'PhoneType' => 'DEFAULT',
          'PhoneNumber' => $node->field_cu_phone->value,
        ]);
      }
      $names = explode(' ', $main_contact->title);
    }
    $contacts->get(0)->get('FirstName')->setValue(array_shift($names));
    $contacts->get(0)->get('LastName')->setValue(implode(' ', $names));

    return $contacts;
  }

  /**
   * Create a Customer/Contact in Xero
   *
   * @param Node $node
   *   Node to process.
   *
   * @return bool
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function sync(Node $node) {
    $settings = \Drupal::configFactory()->get('se_xero.settings');
    if (!$settings->get('system.enabled')) {
      return FALSE;
    }

    // Setup the data structure
    $list_definition = $this->typedDataManager->createListDataDefinition('xero_contact');
    /** @var \Drupal\xero\Plugin\DataType\XeroItemList $contacts */
    $contacts = $this->typedDataManager->create($list_definition, []);

    // Setup the query.
    $xeroQuery = $this->xeroQueryFactory->get();

    // Setup the values.
    $contacts = $this->setContactValues($settings, $contacts, $node);

    // Check if its an existing contact that is to be updated vs new contact.
    if ($contact = $this->lookupContact($node)) {
      // Set the ContactID so it the values we're sending act as updates.
      $xeroQuery->setId($contact->get('ContactID')->getValue());
    }

    $xeroQuery->setType('xero_contact')
      ->setData($contacts)
      ->setMethod('post');

    $result = $xeroQuery->execute();

    if ($result === FALSE) {
      $this->logger->log(LogLevel::ERROR, (string) new FormattableMarkup('Cannot create contact @customer, operation failed.', [
        '@customer' => $node->title->value,
      ]));
      return FALSE;
    }

    if ($result->count() > 0) {
      /** @var \Drupal\xero\Plugin\DataType\Contact $createdXeroContact */
      $createdXeroContact = $result->get(0);
      $remote_id = $createdXeroContact->get('ContactID')->getValue();
      $node->set('se_xero_uuid', $remote_id);
      $node->xero_syncing = TRUE;
      $node->save();

      $this->logger->log(LogLevel::INFO, (string) new FormattableMarkup('Created contact @customer with remote id @remote_id.', [
        '@customer' => $node->title->value,
        '@remote_id' => $remote_id,
      ]));
      return TRUE;
    }
    return FALSE;
  }

}
