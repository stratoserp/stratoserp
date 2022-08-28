<?php

declare(strict_types=1);

namespace Drupal\se_subscription_invoice\Service;

/**
 * Provide an interface for the invoice subscription service.
 */
interface SubscriptionInvoiceServiceInterface {

  /**
   * Retrieve the list of customers with subscriptions that match this day.
   *
   * @param int $customerId
   *   Pass in a customer id to restrict subscription processing to a customer.
   *
   * @return array
   *   And array of invoices.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processDateSubscriptions(int $customerId): array;

  /**
   * Process subscriptions that are not set to use the customer date.
   *
   * @param int $customerId
   *   Pass in a customer id to restrict subscription processing to a customer.
   *
   * @return array
   *   An array of invoices.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processSubscriptions(int $customerId): array;

}
