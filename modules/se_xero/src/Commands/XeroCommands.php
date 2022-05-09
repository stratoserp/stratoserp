<?php

declare(strict_types=1);

namespace Drupal\se_xero\Commands;

use Drupal\Core\Session\UserSession;
use Drush\Commands\DrushCommands;
use Drupal\Core\Session\AccountSwitcherInterface;

/**
 * A Drush commandfile.
 */
class XeroCommands extends DrushCommands {

  /**
   * The account switcher service.
   *
   * @var \Drupal\Core\Session\AccountSwitcherInterface
   */
  protected $accountSwitcher;

  /**
   * SimplesitemapCommands constructor.
   *
   * @param \Drupal\Core\Session\AccountSwitcherInterface $account_switcher
   *   The account switching service.
   */
  public function __construct(AccountSwitcherInterface $account_switcher) {
    $this->accountSwitcher = $account_switcher;
  }

  /**
   * Sync up customer changes since the last sync with xero.
   *
   * @command se:sync-customers
   * @aliases sync-customers
   */
  public function syncCustomers(): ?bool {
    $this->accountSwitcher->switchTo(new UserSession(['uid' => 1]));
    $settings = \Drupal::configFactory()->get('se_xero.settings');
    if (!$settings->get('system.enabled')) {
      return FALSE;
    }
    $timestamp = ($settings->get('customer.sync_timestamp') ?? 0);

    // Retrieve list of customers changed since last sync.
    $ids = \Drupal::entityQuery('se_customer')
      ->condition('changed', $timestamp, '>')
      ->sort('created')
      ->range(0, 20)
      ->execute();

    // Loop through list of customers, loading and syncing.
    $service = \Drupal::service('se_xero.contact_service');
    $storage = \Drupal::entityTypeManager()->getStorage('se_customer');
    foreach ($ids as $id) {
      /** @var \Drupal\se_customer\Entity\Customer $customer */
      $customer = $storage->load($id);
      $service->sync($customer);
      // $settings->set('customer.sync_timestamp', $customer->changed);
    }
    $this->accountSwitcher->switchBack();
  }

  /**
   * Sync up invoices changed since the last sync with xero.
   *
   * @command se:sync-invoices
   * @aliases sync-invoices
   */
  public function syncInvoices(): ?bool {
    $settings = \Drupal::configFactory()->get('se_xero.settings');
    if (!$settings->get('system.enabled')) {
      return FALSE;
    }
    $timestamp = ($settings->get('invoice.sync_timestamp') ?? 0);

    // Retrieve list of nodes changed since last sync.
    $ids = \Drupal::entityQuery('se_invoice')
      ->condition('changed', $timestamp, '>')
      ->sort('created')
      ->range(0, 20)
      ->execute();

    // Loop through list of invoices, loading and syncing.
    $service = \Drupal::service('se_xero.invoice_service');
    $storage = \Drupal::entityTypeManager()->getStorage('se_invoice');
    foreach ($ids as $id) {
      /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
      $invoice = $storage->load($id);
      if (!$service->sync($invoice)) {
        // Log error message.
      }
      // $settings->set('customer.sync_timestamp', $invoice->changed);
    }
  }

}
