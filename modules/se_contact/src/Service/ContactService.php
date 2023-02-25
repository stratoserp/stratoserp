<?php

declare(strict_types=1);

namespace Drupal\se_contact\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\se_customer\Service\CustomerServiceInterface;
use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Contact service class for common contact related manipulation.
 */
class ContactService implements ContactServiceInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected ConfigFactory $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected EntityTypeManager $entityTypeManager;

  /**
   * The customer service.
   *
   * @var \Drupal\se_customer\Service\CustomerServiceInterface
   */
  protected CustomerServiceInterface $customerService;

  /**
   * ContactService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Provide a config factory to the constructor.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Provide an entityTypeManager to the constructor.
   * @param \Drupal\se_customer\Service\CustomerServiceInterface $customerService
   *   Provide the customer service to the constructor.
   */
  public function __construct(ConfigFactory $configFactory, EntityTypeManager $entityTypeManager, CustomerServiceInterface $customerService) {
    $this->configFactory = $configFactory;
    $this->entityTypeManager = $entityTypeManager;
    $this->customerService = $customerService;
  }

  /**
   * {@inheritdoc}
   */
  public function loadMainContactsByCustomer(StratosEntityBaseInterface $entity): array {
    // If no main contact term is selected, bail.
    $config = $this->configFactory->get('se_contact.settings');
    if (!$termId = (int) $config->get('main_contact_term')) {
      return [];
    }

    return $this->loadContactsFromEntity($entity, $termId);
  }

  /**
   * {@inheritdoc}
   */
  public function loadDefaultContactsFromEntity(StratosEntityBaseInterface $entity): array {
    $contactTypes = $this->determineContactTermId($entity->bundle());

    return $this->loadContactsFromEntity($entity, $contactTypes);
  }

  /**
   * {@inheritdoc}
   */
  public function loadContactsFromEntity(StratosEntityBaseInterface $entity, int $contactType = NULL): array {
    // Get the customer from any entity.
    $customer = $this->customerService->lookupCustomer($entity);

    $stg = $this->entityTypeManager->getStorage('se_contact');

    $query = $stg->getQuery()
      ->condition('se_cu_ref', $customer->id());

    if ($contactType) {
      $query->condition('se_type_ref', $contactType);
    }
    $query->accessCheck(FALSE);

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function contactsToEmails(array $contactIdList): array {
    $contactList = [];

    $stg = $this->entityTypeManager->getStorage('se_contact');

    /** @var \Drupal\se_contact\Entity\ContactInterface $contact */
    foreach ($stg->loadMultiple($contactIdList) as $contact) {
      if (isset($contact->se_email->value) && stripos($contact->se_email->value, '@')) {
        $contactList[$contact->se_email->value] = $contact->getName() . ' - ' . $contact->se_email->value;
      }
    }

    return $contactList;
  }

  /**
   * Given a bundle string, return the sensible contact term type id.
   *
   * @param string $bundle
   *   The entity bundle type to check.
   *
   * @return int
   *   The term id for the contact type.
   */
  protected function determineContactTermId($bundle) {
    $config = $this->configFactory->get('se_contact.settings');

    // This should be a configurable matrix sort of UI thing.
    $type = match ($bundle) {
      'se_invoice', 'se_statement' => 'accounting_contact_term',
      default => 'main_contact_term',
    };

    return (int) $config->get($type);
  }

}
