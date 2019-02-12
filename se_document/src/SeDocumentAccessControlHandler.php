<?php

namespace Drupal\se_document;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Document entity.
 *
 * @see \Drupal\se_document\Entity\SeDocument.
 */
class SeDocumentAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\se_document\Entity\SeDocumentInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished document entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published document entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit document entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete document entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add document entities');
  }

}
