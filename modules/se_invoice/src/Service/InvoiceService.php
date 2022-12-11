<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_payment\Traits\PaymentTrait;
use Drupal\taxonomy\Entity\Term;

/**
 * Service for various invoice related functions.
 */
class InvoiceService implements InvoiceServiceInterface {

  use PaymentTrait;

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
   * SeContactService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory being used.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager being used.
   */
  public function __construct(ConfigFactory $config_factory, EntityTypeManager $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function checkInvoiceStatus(Invoice $invoice, int $payment = NULL): string {
    if ($payment === $invoice->getTotal()
      || $payment === $invoice->getOutstanding()
      || (int) $invoice->getOutstanding() === 0) {
      return 'closed';
    }

    return 'open';
  }

}
