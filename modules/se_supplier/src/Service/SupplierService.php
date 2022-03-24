<?php

declare(strict_types=1);

namespace Drupal\se_supplier\Service;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\se_supplier\Entity\SupplierInterface;
use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Supplier service class for common custom related manipulations.
 */
class SupplierService implements SupplierServiceInterface {

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
   * SupplierService constructor.
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
  public function lookupSupplier(StratosEntityBaseInterface $entity) {
    if ($entity instanceof SupplierInterface) {
      return $entity;
    }

    if (isset($entity->se_cu_ref) && $supplier = $entity->getSupplier()) {
      /** @var \Drupal\se_supplier\Entity\Supplier $supplier */
      return $supplier;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getInvoiceDayTimestamp($supplier): int {
    return DateTimePlus::createFromFormat(
      'Y-m-d H:i:s',
      date('Y-m-') . sprintf('%02d', $supplier->se_invoice_day->value) . ' 00:00:00'
    )->getTimestamp();
  }

}
