<?php

declare(strict_types=1);

namespace Drupal\se_subscription_invoice\Service;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\se_business\Entity\Business;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_subscription\Entity\Subscription;

/**
 * Service to create invoices out of subscriptions.
 */
class SubscriptionInvoiceService {

  use StringTranslationTrait;

  protected EntityTypeManagerInterface $entityTypeManager;
  protected LoggerChannel $loggerChannel;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   EntityTypeManager for later.
   * @param \Drupal\Core\Logger\LoggerChannel $loggerChannel
   *   Logger for later.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerChannel $loggerChannel) {
    $this->entityTypeManager = $entityTypeManager;
    $this->loggerChannel = $loggerChannel;
  }

  /**
   * Retrieve the list of businesses with subscriptions that match this day.
   *
   * @return array
   *   And array of invoices.
   *
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
      ->condition('se_invoice_day', 0, '>')
      ->condition('se_invoice_day', date('d'), '<=');

    // Load subscriptions to create line items.
    foreach ($query->execute() as $businessId) {
      $business = Business::load($businessId);
      $items = $this->subscriptionsToItems($business);

      if (count($items) && $invoice = $this->subscriptionsToInvoice($business, $items)) {
        $invoices[] = $invoice;
        $this->updateDueDate($items);
      }
    }

    return $invoices;
  }

  /**
   * Process subscriptions that are not set to use the business date.
   *
   * @return array
   *   An array of invoices.
   *
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
      ->condition('se_next_due', date('U'), '<=')
      ->condition('se_use_bu_due', 0);

    // Load subscriptions to create line items.
    foreach ($query->execute() as $businessId) {
      $business = Business::load($businessId);
      $items = $this->subscriptionsToItems($businessId);
      if (count($items) && $invoice = $this->subscriptionsToInvoice($business, $items)) {
        $invoices[] = $invoice;
        $this->updateDueDate($items);
      }
    }

    return $invoices;
  }

  /**
   * Update the next due date for a subscription.
   *
   * @param array $subscriptions
   *   The list of successful subscriptions to update.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function updateDueDate(array $subscriptions): void {
    foreach ($subscriptions as $lines) {
      $subscription = Subscription::load($lines['subscription_id']);
      $seconds = \Drupal::service('duration_field.service')->getSecondsFromDurationString($subscription->se_period->duration);
      // Do we need to worry about drift?
      $subscription->se_next_due->value += $seconds;
      $subscription->save();
    }
  }

  /**
   * Process subscriptions for a specific business.
   *
   * @param \Drupal\se_business\Entity\Business $business
   *   The business we're doing subscription invoicing for.
   *
   * @return array
   *   The invoice lines.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function subscriptionsToItems(Business $business): array {
    // Build a query for subscriptions due for this business.
    $query = $this->entityTypeManager
      ->getStorage('se_subscription')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('se_next_due', date('U'), '<=')
      ->condition('se_bu_ref', $business->id());

    return $this->subscriptionsToInvoiceLines($query->execute());
  }

  /**
   * Create an invoice from the subscription lines and business.
   *
   * @param \Drupal\se_business\Entity\Business $business
   *   The business we're doing subscription invoicing for.
   * @param \Drupal\se_subscription\Entity\Subscription[] $subscriptions
   *   The list of subscriptions.
   *
   * @return \Drupal\se_invoice\Entity\Invoice
   *   The completed invoice
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function subscriptionsToInvoice(Business $business, array $subscriptions): Invoice {
    $invoiceContent = [
      'type' => 'se_invoice',
      'name' => '',
      'se_bu_ref' => [
        'target_id' => $business->id(),
        'target_type' => 'se_business',
      ],
      'se_phone' => $business->se_phone,
      'se_email' => $business->se_email,
      'se_item_lines' => $subscriptions,
    ];

    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = Invoice::create($invoiceContent);
    $date = new DateTimePlus('now', date_default_timezone_get());
    $entityTitle = $this->t('@business - @type - @date', [
      '@business' => $business->getName(),
      '@type' => $this->formatPlural(count($subscriptions), 'Subscription', 'Subscriptions'),
      '@date' => $date->format('d/m/Y'),
    ]);
    $invoice->setName($entityTitle);
    $invoice->save();

    return $invoice;
  }

  /**
   * Create the item lines for an invoice.
   *
   * @param \Drupal\se_subscription\Entity\Subscription[] $subscriptions
   *   The list of subscriptions to process.
   *
   * @return array
   *   The item line array for the invoice.
   */
  private function subscriptionsToInvoiceLines(array $subscriptions): array {
    $lines = [];

    foreach ($subscriptions as $sub) {
      if (!$subscription = Subscription::load($sub)) {
        $this->loggerChannel->critical('Unable to load subscription %s', ['%s' => print_r($sub, TRUE)]);
        return [];
      }

      // Subscriptions are limited to a single line.
      $line = $subscription->se_item_lines[0];
      switch ($subscription->bundle()) {
        case 'se_email_account':
          $extra = $subscription->se_email_address->value;
          break;

        case 'se_domain_name':
        case 'se_domain_hosting':
          $extra = $subscription->se_domain_name->value;
          break;

        default:
          $extra = '';
      }

      $lines[] = [
        'target_type' => $line->target_type,
        'target_id' => $line->target_id,
        'quantity' => $line->quantity,
        'note' => $extra,
        'price' => $line->price,
        'subscription_id' => $subscription->id(),
      ];
    }

    return $lines;
  }

}
