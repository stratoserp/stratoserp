<?php

namespace Drupal\se_xero\Commands;

use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 */
class SeXeroCommands extends DrushCommands {

  /**
   * Sync up customers changes since the last sync with xero.
   *
   * @command se:sync-customers
   * @aliases sync-customers
   */
  public function syncCustomers() {
    $settings = \Drupal::configFactory()->get('se_xero.settings');
    if (!$settings->get('system.enabled')) {
      return FALSE;
    }
    $timestamp = ($settings->get('customer.sync_timestamp') ?: 0);

    // Retrieve list of nodes changed since last sync.
    $ids = \Drupal::entityQuery('node')
      ->condition('type', 'se_customer')
      ->condition('changed', $timestamp, '>')
      ->sort('created')
      ->range(0,20)
      ->execute();

    // Loop through list of nodes, loading and syncing.
    $service = \Drupal::service('se_xero.contact_service');
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    foreach ($ids as $id) {
      /** @var \Drupal\node\Entity\Node $node */
      $node = $node_storage->load($id);
      $service->sync($node);
      //$settings->set('customer.sync_timestamp', $node->changed);
    }
  }

  /**
   * Sync up invoices changed since the last sync with xero.
   * @command se:sync-invoices
   * @aliases sync-invoices
   */
  public function syncInvoices() {
    $settings = \Drupal::configFactory()->get('se_xero.settings');
    if (!$settings->get('system.enabled')) {
      return FALSE;
    }
    $timestamp = ($settings->get('invoice.sync_timestamp') ?: 0);

    // Retrieve list of nodes changed since last sync.
    $ids = \Drupal::entityQuery('node')
      ->condition('type', 'se_invoice')
      ->condition('changed', $timestamp, '>')
      ->sort('created')
      ->range(0,20)
      ->execute();

    // Loop through list of nodes, loading and syncing.
    $service = \Drupal::service('se_xero.invoice_service');
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    foreach ($ids as $id) {
      /** @var \Drupal\node\Entity\Node $node */
      $node = $node_storage->load($id);
      if (!$service->sync($node)) {
        // Log error message.
      }
      //$settings->set('customer.sync_timestamp', $node->changed);
    }
  }

}
