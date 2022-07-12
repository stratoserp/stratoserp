<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Goods receipt entity.
 *
 * @see \Drupal\se_goods_receipt\Entity\GoodsReceipt.
 */
class GoodsReceiptAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\se_goods_receipt\Entity\GoodsReceiptInterface $entity */

    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view changes to goods receipt entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view goods receipt entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit goods receipt entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete goods receipt entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add goods receipt entities');
  }

}
