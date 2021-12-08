<?php

declare(strict_types=1);

namespace Drupal\se_subscription_invoice\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\se_business\Entity\Business;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_subscription\Entity\Subscription;

class SubscriptionInvoiceService {

  protected EntityTypeManagerInterface $entityTypeManager;
  protected LoggerChannel $loggerChannel;

  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerChannel $loggerChannel) {
    $this->entityTypeManager = $entityTypeManager;
    $this->loggerChannel = $loggerChannel;
  }

  /**
   * Retrieve the list of businesses with subscriptions that match this day.
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processDateSubscriptions(): array {
    $invoices = [];

    $query = $this->entityTypeManager
      ->getStorage('se_business')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('se_bu_invoice_day_of_month', date('d'), '<=');

    // Load subscriptions to create line items
    foreach ($query->execute() as $businessId) {
      $business = Business::load($businessId);
      $subscriptions = $this->processBusinessSubscriptions($businessId);

      if (count($subscriptions)) {
        $invoices[] = $this->subscriptionsToInvoice($business, $subscriptions);
      }
    }

    return $invoices;
  }

  /**
   * Process subscriptions for a specific business.
   *
   * @param $businessId
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function processBusinessSubscriptions($businessId): array {
    // Build a query for subscriptions due for this business.
    $query = $this->entityTypeManager
      ->getStorage('se_subscription')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('se_su_next_due', date('U'), '<=')
      ->condition('se_bu_ref', $businessId);

    return $this->subscriptionsToInvoiceLines($query->execute());
  }

  /**
   * Process subscriptions that are not set to use the business date.
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processSubscriptions(): array {
    $invoices = [];

    // Build a query for subscriptions due not using business date.
    $query = $this->entityTypeManager
      ->getStorage('se_subscription')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('se_su_next_due', date('U'), '<=')
      ->condition('se_su_use_bu_due', 0);

    // Load subscriptions to create line items
    foreach ($query->execute() as $businessId) {
      $business = Business::load($businessId);
      $subscriptions = $this->processBusinessSubscriptions($businessId);
      if (count($subscriptions)) {
        $invoices[] = $this->subscriptionsToInvoice($business, $subscriptions);
      }
    }

    return $invoices;
  }

  /**
   * Create an invoice from the subscription lines and business.
   *
   * @param $business
   * @param $subscriptions
   *
   * @return \Drupal\se_invoice\Entity\Invoice
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function subscriptionsToInvoice($business, $subscriptions): Invoice {
    $invoiceContent = [
      'type' => 'se_invoice',
      'name' => '',
      'se_bu_ref' => [
        'target_id' => $business->id(),
        'target_type' => 'se_business',
      ],
      'se_in_phone' => $business->se_bu_phone,
      'se_in_email' => $business->se_bu_email,
      'se_in_lines' => $subscriptions,
    ];

    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = Invoice::create($invoiceContent);
    $invoice->setName('Test');
    $invoice->getTotal();
    $invoice->save();

    return $invoice;
  }

  /**
   * Create the item lines for an invoice.
   *
   * @param $subscriptions
   *
   * @return array
   */
  private function subscriptionsToInvoiceLines($subscriptions): array {
    $lines = [];

    foreach ($subscriptions as $index => $sub) {
      if (!$subscription = Subscription::load($sub)) {
        $this->loggerChannel->critical('Unable to load subscription %s', ['%s' => print_r($sub, TRUE)]);
        return [];
      }

      // Subscriptions are limited to a single line.
      $line = $subscription->se_su_lines[0];

      $lines[] = [
        'target_type' => $line->target_type,
        'target_id' => $line->target_id,
        'quantity' => $line->quantity,
        'price' => $line->price,
      ];
    }

    return $lines;
  }

}
