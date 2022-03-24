<?php

declare(strict_types=1);

namespace Drupal\se_xero\Commands;

use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 */
class XeroCommands extends DrushCommands {

  /**
   * Sync up customer changes since the last sync with xero.
   *
   * @command se:sync-customer
   * @aliases sync-customer
   */
  public function syncCustomers(): ?bool {
    $settings = \Drupal::configFactory()->get('se_xero.settings');
    if (!$settings->get('system.enabled')) {
      return FALSE;
    }
    $timestamp = ($settings->get('customer.sync_timestamp') ?? 0);

    // Retrieve list of nodes changed since last sync.
    $ids = \Drupal::entityQuery('se_customer')
      ->condition('changed', $timestamp, '>')
      ->sort('created')
      ->range(0, 20)
      ->execute();

    // Loop through list of nodes, loading and syncing.
    $service = \Drupal::service('se_xero.contact_service');
    $storage = \Drupal::entityTypeManager()->getStorage('se_customer');
    foreach ($ids as $id) {
      /** @var \Drupal\se_customer\Entity\Customer $customer */
      $customer = $storage->load($id);
      $service->sync($customer);
      // $settings->set('customer.sync_timestamp', $node->changed);
    }
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

    // Loop through list of nodes, loading and syncing.
    $service = \Drupal::service('se_xero.invoice_service');
    $storage = \Drupal::entityTypeManager()->getStorage('se_invoice');
    foreach ($ids as $id) {
      /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
      $invoice = $storage->load($id);
      if (!$service->sync($invoice)) {
        // Log error message.
      }
      // $settings->set('customer.sync_timestamp', $node->changed);
    }
  }

}
