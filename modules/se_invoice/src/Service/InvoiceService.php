<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\se_business\Entity\Business;
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
   * @var configFactory
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var entityTypeManager
   */
  protected $entityTypeManager;

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
   * Retrieve the outstanding invoices for a business.
   *
   * @param \Drupal\se_business\Entity\Business $business
   *   The Business entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   The found entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getOutstandingInvoices(Business $business): array {
    $query = \Drupal::entityQuery('se_invoice');
    $query->condition('se_bu_ref', $business->id());
    $query->condition('se_status_ref', $this->getOpenTerm()->id());
    $entityIds = $query->execute();

    return \Drupal::entityTypeManager()
      ->getStorage('se_invoice')
      ->loadMultiple($entityIds);
  }

  /**
   * Check if an invoice should be marked as paid.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The Invoice entity.
   * @param string $payment
   *   The paid amount.
   *
   * @return \Drupal\taxonomy\Entity\Term
   *   The invoice status
   */
  public function checkInvoiceStatus(Invoice $invoice, $payment = NULL): Term {
    if ($payment === (string) $invoice->getTotal()
      || $payment === (string) $invoice->getOutstanding()
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
    // Set the outstanding amount field.
    if ($invoice->isNew()) {
      $invoiceTotal = $invoice->getTotal();
      $invoice->setOutstanding($invoiceTotal);
    }
    else {
      // Store the old invoice for comparisons in later events.
      $invoice->storeOldInvoice();

      // Retrieve the new invoice total.
      $invoiceTotal = $invoice->getTotal();

      // If the balance owning on this invoice is different from before, adjust.
      if ($oldInvoice = $invoice->getOldInvoice()) {
        $oldOutstanding = $oldInvoice->getOutstanding();
        $difference = $invoiceTotal - $oldOutstanding;
        $invoice->setOutstanding($oldOutstanding + $difference);
      }
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
  public function statusTotalUpdate(Invoice $invoice): void {
    // Update the business balance.
    if ($business = $invoice->getBusiness()) {

      // Retrieve the new invoice outstanding amount.
      $invoiceOutstanding = $invoice->getOutstanding();

      if ($oldInvoice = $invoice->getOldInvoice()) {
        $oldOutstanding = $oldInvoice->getOutstanding();
        $difference = $invoiceOutstanding - $oldOutstanding;
        $business->adjustBalance($difference);
      }
      else {
        $business->adjustBalance($invoiceOutstanding);
      }
    }
  }

  /**
   * Update customer balance if an invoice is deleted.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   Invoice to update.
   */
  public function deleteUpdate(Invoice $invoice) {
    if ($business = $invoice->getBusiness()) {
      // @todo What if there were payments?
      $business->adjustBalance($invoice->getTotal() * -1);
    }
  }

}
