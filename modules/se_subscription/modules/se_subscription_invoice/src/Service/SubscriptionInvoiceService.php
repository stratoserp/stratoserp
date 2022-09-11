<?php

declare(strict_types=1);

namespace Drupal\se_subscription_invoice\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\duration_field\Service\DurationService;
use Drupal\duration_field\Service\DurationServiceInterface;
use Drupal\se_customer\Entity\Customer;
use Drupal\se_customer\Service\CustomerService;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_subscription\Entity\Subscription;

/**
 * Service to create invoices out of subscriptions.
 */
class SubscriptionInvoiceService implements SubscriptionInvoiceServiceInterface {

  use StringTranslationTrait;

  protected EntityTypeManagerInterface $entityTypeManager;
  protected LoggerChannel $loggerChannel;
  protected CustomerService $customerService;
  protected DurationService $durationService;

  /**
   * Constructor for this service.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   EntityTypeManager for later.
   * @param \Drupal\Core\Logger\LoggerChannel $loggerChannel
   *   Logger for later.
   * @param \Drupal\se_customer\Service\CustomerService $customerService
   *   The customer service.
   * @param \Drupal\duration_field\Service\DurationService $durationService
   *   The duration service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerChannelInterface $loggerChannel, CustomerService $customerService, DurationServiceInterface $durationService) {
    $this->entityTypeManager = $entityTypeManager;
    $this->loggerChannel = $loggerChannel;
    $this->customerService = $customerService;
    $this->durationService = $durationService;
  }

  /**
   * {@inheritdoc}
   */
  public function processDateSubscriptions($customerId = NULL): array {
    $invoices = [];

    // Load the customer ids for customers that have a invoice day set.
    $query = $this->entityTypeManager
      ->getStorage('se_customer')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('se_invoice_day', 0, '>')
      ->condition('se_invoice_day', date('d'), '<=');

    if ($customerId) {
      $query->condition('id', $customerId);
    }

    // Load subscriptions to create line items.
    foreach ($query->execute() as $customerId) {
      if ($customer = Customer::load($customerId)) {
        $items = $this->subscriptionsToItems($customer);

        if (count($items) && $invoice = $this->subscriptionsToInvoice($customer, $items)) {
          $invoices[] = $invoice;
          $this->updateDueDate($items);
        }
      }
    }

    return $invoices;
  }

  /**
   * {@inheritdoc}
   */
  public function processSubscriptions($customerId = NULL): array {
    $invoices = [];

    // Build a query for subscriptions due not using customer date.
    $query = $this->entityTypeManager
      ->getStorage('se_subscription')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('se_next_due', date('U'), '<=')
      ->condition('se_use_bu_due', 0);

    if ($customerId) {
      $query->condition('se_cu_ref', $customerId);
    }

    // Load subscriptions to create line items.
    foreach ($query->execute() as $customerId) {
      if ($customer = Customer::load($customerId)) {
        $items = $this->subscriptionsToItems($customer);

        if (count($items) && $invoice = $this->subscriptionsToInvoice($customer, $items)) {
          $invoices[] = $invoice;
          $this->updateDueDate($items);
        }
      }
    }

    return $invoices;
  }

  /**
   * Update the next due date for a subscription.
   *
   * @param array $subscriptions
   *   The list of successful subscriptions to update.
   */
  private function updateDueDate(array $subscriptions): void {
    foreach ($subscriptions as $lines) {
      try {
        $subscription = Subscription::load($lines['subscription_id']);

        // Get a mktime sort of array from the duration.
        $interval = $this->durationService->getDateIntervalFromDurationString($subscription->se_period->duration);

        // Convert that into a string with day resolution.
        $duration = $this->durationService->getHumanReadableStringFromDateInterval($interval, [
          'y' => TRUE,
          'm' => TRUE,
          'd' => TRUE,
          'h' => FALSE,
          'i' => FALSE,
          's' => FALSE,
        ]);

        // Calculate the future date.
        $startOfDay = new \DateTime('midnight ' . $duration);
        $subscription->se_next_due->value = $startOfDay->getTimestamp();
        $subscription->save();
      }
      catch (\Exception $e) {
        $this->loggerChannel->critical('Unable to load subscription');
      }
    }
  }

  /**
   * Process subscriptions for a specific customer.
   *
   * @param \Drupal\se_customer\Entity\Customer $customer
   *   The customer we're doing subscription invoicing for.
   *
   * @return array
   *   The invoice lines.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function subscriptionsToItems(Customer $customer): array {
    // Build a query for subscriptions due for this customer.
    $query = $this->entityTypeManager
      ->getStorage('se_subscription')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('se_next_due', date('U'), '<=')
      ->condition('se_cu_ref', $customer->id());

    return $this->subscriptionsToInvoiceLines($query->execute());
  }

  /**
   * Create an invoice from the subscription lines and customer.
   *
   * @param \Drupal\se_customer\Entity\Customer $customer
   *   The customer we're doing subscription invoicing for.
   * @param \Drupal\se_subscription\Entity\Subscription[] $subscriptions
   *   The list of subscriptions.
   *
   * @return \Drupal\se_invoice\Entity\Invoice
   *   The completed invoice
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function subscriptionsToInvoice(Customer $customer, array $subscriptions): Invoice {
    $invoiceContent = [
      'type' => 'se_invoice',
      'name' => '',
      'se_cu_ref' => [
        'target_id' => $customer->id(),
        'target_type' => 'se_customer',
      ],
      'se_phone' => $customer->se_phone,
      'se_email' => $customer->se_email,
      'se_item_lines' => $subscriptions,
    ];

    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = Invoice::create($invoiceContent);

    // Set the timestamp on the invoice to the customer invoice day.
    $invoice->setCreatedTime($this->customerService->getInvoiceDayTimestamp($customer));

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
        'cost' => $line->cost,
        'subscription_id' => $subscription->id(),
      ];
    }

    return $lines;
  }

}
