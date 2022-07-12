<?php

declare(strict_types=1);

namespace Drupal\se_relationship;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Relationship entity.
 *
 * @see \Drupal\se_relationship\Entity\Relationship.
 */
class RelationshipAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\se_relationship\Entity\RelationshipInterface $entity */

    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view changes to relationship entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view relationship entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit relationship entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete relationship entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add relationship entities');
  }

}
