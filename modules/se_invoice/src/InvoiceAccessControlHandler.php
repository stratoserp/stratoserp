<?php

declare(strict_types=1);

namespace Drupal\se_invoice;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Invoice entity.
 *
 * @see \Drupal\se_invoice\Entity\Invoice.
 */
class InvoiceAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\se_invoice\Entity\InvoiceInterface $entity */

    switch ($operation) {

      case 'view':

        return AccessResult::allowedIfHasPermission($account, 'view invoice entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit invoice entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete invoice entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add invoice entities');
  }

}
