<?php

declare(strict_types=1);

namespace Drupal\se_contact;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Contact entity.
 *
 * @see \Drupal\se_contact\Entity\Contact.
 */
class ContactAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\se_contact\Entity\ContactInterface $entity */

    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view changes to contact entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view contact entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit contact entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete contact entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add contact entities');
  }

}
