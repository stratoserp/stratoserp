<?php

namespace Drupal\se_purchase_order;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the PurchaseOrder entity.
 *
 * @see \Drupal\se_purchase_order\Entity\PurchaseOrder.
 */
class PurchaseOrderAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\se_purchase_order\Entity\PurchaseOrderInterface $entity */

    switch ($operation) {

      case 'view':

        return AccessResult::allowedIfHasPermission($account, 'view purchase order entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit purchase order entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete purchase order entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add purchase order entities');
  }

}
