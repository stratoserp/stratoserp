<?php

namespace Drupal\se_information;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Information entity.
 *
 * @see \Drupal\se_information\Entity\Information.
 */
class InformationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\se_information\Entity\InformationInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished information entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published information entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit information entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete information entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add information entities');
  }

}
