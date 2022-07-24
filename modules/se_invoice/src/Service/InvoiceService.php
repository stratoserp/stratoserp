<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\se_customer\Entity\customer;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_payment\Traits\PaymentTrait;
use Drupal\taxonomy\Entity\Term;

/**
 * Service for various invoice related functions.
 */
class InvoiceService {

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
   * Check if an invoice should be marked as paid.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The Invoice entity.
   * @param int|null $payment
   *   The paid amount.
   *
   * @return \Drupal\taxonomy\Entity\Term
   *   The invoice status
   */
  public function checkInvoiceStatus(Invoice $invoice, int $payment = NULL): Term {
    if ($payment === $invoice->getTotal()
      || $payment === $invoice->getOutstanding()
      || (int) $invoice->getOutstanding() === 0) {
      return $this->getClosedTerm();
    }

    return $this->getOpenTerm();
  }

  /**
   * Retrieve the term user for open status.
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   *   The term for open status.
   */
  public function getOpenTerm(): ?Term {
    if ($term = $this->configFactory->get('se_invoice.settings')->get('open_term')) {
      return Term::load($term);
    }
    return NULL;
  }

  /**
   * Retrieve the term user for paid status.
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   *   The term for paid status.
   */
  public function getClosedTerm(): ?Term {
    if ($term = $this->configFactory->get('se_invoice.settings')->get('closed_term')) {
      return Term::load($term);
    }
    return NULL;
  }

  /**
   * Setup fields and more in preparation for saving/updating an invoice.
   *
   * Some shenanigans in here to cope with invoice amounts
   * being adjusted.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The invoice to work with.
   */
  public function preSave(Invoice $invoice): void {
    // Set the outstanding amount field only for new invoices.
    if ($invoice->isNew()) {
      $invoice->setOutstanding($invoice->getTotal());
    }

    // Needs to be after outstanding is calculated.
    $invoice->se_status_ref->entity = $this->checkInvoiceStatus($invoice);
  }

  /**
   * Update invoice total on update.
   *
   * Some shenanigans in here to cope with invoice amounts
   * being adjusted.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   Invoice to update.
   */
  public function statusUpdate(Invoice $invoice): void {
    // Needs to be after outstanding is calculated.
    $invoice->se_status_ref->entity = $this->checkInvoiceStatus($invoice);
  }

  /**
   * Update customer balance if an invoice is deleted.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   Invoice to update.
   */
  public function deleteUpdate(Invoice $invoice) {
    if ($customer = $invoice->getcustomer()) {
      // @todo What if there were payments?
      $customer->adjustBalance($invoice->getTotal() * -1);
    }
  }

}
