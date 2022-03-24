<?php

declare(strict_types=1);

namespace Drupal\se_supplier;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Supplier entity.
 *
 * @see \Drupal\se_supplier\Entity\Supplier.
 */
class SupplierAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\se_supplier\Entity\SupplierInterface $entity */

    switch ($operation) {

      case 'view':

        return AccessResult::allowedIfHasPermission($account, 'view supplier entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit supplier entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete supplier entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add supplier entities');
  }

}
