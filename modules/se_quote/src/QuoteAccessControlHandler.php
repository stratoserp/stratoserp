<?php

declare(strict_types=1);

namespace Drupal\se_quote;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Quote entity.
 *
 * @see \Drupal\se_quote\Entity\Quote.
 */
class QuoteAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\se_quote\Entity\QuoteInterface $entity */

    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view changes to quote entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view quote entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit quote entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete quote entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add quote entities');
  }

}
