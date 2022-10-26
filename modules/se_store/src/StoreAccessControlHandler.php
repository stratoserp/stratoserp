<?php

declare(strict_types=1);

namespace Drupal\se_store;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Store entity.
 *
 * @see \Drupal\se_store\Entity\Store.
 */
class StoreAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\se_store\Entity\StoreInterface $entity */

    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view changes to store entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view store entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit store entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete store entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add store entities');
  }

}
