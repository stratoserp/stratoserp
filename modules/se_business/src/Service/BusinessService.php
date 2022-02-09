<?php

declare(strict_types=1);

namespace Drupal\se_business\Service;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\se_business\Entity\BusinessInterface;
use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Business service class for common custom related manipulations.
 */
class BusinessService implements BusinessServiceInterface {

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
   * BusinessService constructor.
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
  public function lookupBusiness(StratosEntityBaseInterface $entity) {
    if ($entity instanceof BusinessInterface) {
      return $entity;
    }

    if (isset($entity->se_bu_ref) && $business = $entity->getBusiness()) {
      /** @var \Drupal\se_business\Entity\Business $business */
      return $business;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getInvoiceDayTimestamp($business): int {
    return DateTimePlus::createFromFormat(
      'Y-m-d H:i:s',
      date('Y-m-') . sprintf('%02d', $business->se_invoice_day->value) . ' 00:00:00'
    )->getTimestamp();
  }

}
