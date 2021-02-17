<?php

declare(strict_types=1);

namespace Drupal\se_business;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Business entity.
 *
 * @see \Drupal\se_business\Entity\Business.
 */
class BusinessAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\se_business\Entity\BusinessInterface $entity */

    switch ($operation) {

      case 'view':

        return AccessResult::allowedIfHasPermission($account, 'view business entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit business entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete business entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add business entities');
  }

}
