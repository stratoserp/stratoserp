<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\se_business\Entity\Business;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_payment\Traits\PaymentTrait;
use Drupal\se_timekeeping\Entity\Timekeeping;
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
   *   The payment amount.
   *
   * @return \Drupal\taxonomy\Entity\Term
   *   The invoice status
   */
  public function checkInvoiceStatus(Invoice $invoice, $payment = NULL): Term {
    if ($payment === $invoice->se_total->value
      || $payment === $invoice->se_outstanding->value
      || (int) $invoice->se_outstanding->value === 0) {
      return $this->getClosedTerm();
    }

    return $this->getOpenTerm();
  }

  /**
   * Retrieve the term user for open status.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\taxonomy\Entity\Term|null
   *   The term for open status.
   */
  public function getOpenTerm() {
    if ($term = \Drupal::configFactory()->get('se_invoice.settings')->get('open_term')) {
      return Term::load($term);
    }
    return NULL;
  }

  /**
   * Retrieve the term user for paid status.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\taxonomy\Entity\Term|null
   *   The term for paid status.
   */
  public function getClosedTerm() {
    if ($term = \Drupal::configFactory()->get('se_invoice.settings')->get('closed_term')) {
      return Term::load($term);
    }
    return NULL;
  }

  /**
   * Store the previous balance of the invoice dynamically.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   Invoice object to store the balance in.
   */
  public function storeBalance(Invoice $invoice) {
    // Store the values on the object before saving for adjustments afterwards.
    $invoice->se_old_total = $invoice->getTotal();
  }

  /**
   * Update invoice total on insert.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   Invoice to update.
   */
  public function statusTotalInsert(Invoice $invoice) {
    $invoice->set('se_status_ref', $this->checkInvoiceStatus($invoice));

    // On insert, the total is outstanding.
    $invoiceBalance = $invoice->getTotal();
    $invoice->set('se_outstanding', $invoiceBalance);

    $business = $invoice->getBusiness();
    $business->adjustBalance($invoiceBalance);
  }

  /**
   * Update invoice total on update.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   Invoice to update.
   */
  public function statusTotalUpdate(Invoice $invoice) {
    $invoice->set('se_status_ref', $this->checkInvoiceStatus($invoice));

    $invoiceBalance = $invoice->getInvoiceBalance();
    $invoice->set('se_outstanding', $invoiceBalance);

    $business = $invoice->getBusiness();
    $business->adjustBalance($invoiceBalance - (int) $invoice->se_old_total);
  }

  /**
   * Update balance if an invoice is deleted.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   Invoice to update.
   */
  public function deleteUpdate(Invoice $invoice) {
    $business = $invoice->getBusiness();
    $business->adjustBalance($invoice->getTotal() * -1);
  }

  /**
   * Loop through the invoice entries and mark the originals as required.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The entity to update timekeeping items.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function timekeepingMarkItemsBilled(Invoice $invoice): void {
    foreach ($invoice->se_item_lines as $itemLine) {
      if ($itemLine->target_type === 'se_timekeeping') {
        /** @var \Drupal\se_timekeeping\Entity\Timekeeping $timekeeping */
        if ($timekeeping = Timekeeping::load($itemLine->target_id)) {
          // @todo Make a service for this?
          $timekeeping->set('se_billed', TRUE);
          $timekeeping->set('se_in_ref', $invoice->id());
          $timekeeping->save();
        }
      }
    }
  }

  /**
   * Loop through the invoice entries and mark the originals as required.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The entity to update timekeeping items.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function timekeepingMarkItemsUnBilled(Invoice $invoice): void {
    foreach ($invoice->se_item_lines as $itemLine) {
      if ($itemLine->target_type === 'se_timekeeping') {
        /** @var \Drupal\se_timekeeping\Entity\Timekeeping $timekeeping */
        if ($timekeeping = Timekeeping::load($itemLine->target_id)) {
          // @todo Make a service for this?
          $timekeeping->set('se_billed', FALSE);
          $timekeeping->set('se_in_ref', NULL);
          $timekeeping->save();
        }
      }
    }
  }

}
