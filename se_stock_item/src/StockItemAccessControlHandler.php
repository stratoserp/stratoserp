<?php

namespace Drupal\se_stock_item;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Stock item entity.
 *
 * @see \Drupal\se_stock_item\Entity\StockItem.
 */
class StockItemAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\se_stock_item\Entity\StockItemInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished stock item entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published stock item entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit stock item entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete stock item entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add stock item entities');
  }

}
