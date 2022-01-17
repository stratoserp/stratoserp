<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Timekeeping entity.
 *
 * @see \Drupal\se_timekeeping\Entity\Timekeeping.
 */
class TimekeepingAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\se_timekeeping\Entity\TimekeepingInterface $entity */

    switch ($operation) {

      case 'view':

        return AccessResult::allowedIfHasPermission($account, 'view timekeeping entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit timekeeping entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete timekeeping entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add timekeeping entities');
  }

}