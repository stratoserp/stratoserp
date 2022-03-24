<?php

declare(strict_types=1);

namespace Drupal\se_customer\Service;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\se_customer\Entity\CustomerInterface;
use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Customer service class for common custom related manipulations.
 */
class CustomerService implements CustomerServiceInterface {

  /**
   * The config factory.
   *
   * @var configFactory
   */
  protected ConfigFactory $configFactory;

  /**
   * The entity type manager.
   *
   * @var entityTypeManager
   */
  protected EntityTypeManager $entityTypeManager;

  /**
   * CustomerService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Provide a config factory to the constructor.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Provide an entityTypeManager to the constructor.
   */
  public function __construct(ConfigFactory $configFactory, EntityTypeManager $entityTypeManager) {
    $this->configFactory = $configFactory;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function lookupCustomer(StratosEntityBaseInterface $entity) {
    if ($entity instanceof CustomerInterface) {
      return $entity;
    }

    if (isset($entity->se_cu_ref) && $customer = $entity->getCustomer()) {
      /** @var \Drupal\se_customer\Entity\Customer $customer */
      return $customer;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getInvoiceDayTimestamp($customer): int {
    return DateTimePlus::createFromFormat(
      'Y-m-d H:i:s',
      date('Y-m-') . sprintf('%02d', $customer->se_invoice_day->value) . ' 00:00:00'
    )->getTimestamp();
  }

}
