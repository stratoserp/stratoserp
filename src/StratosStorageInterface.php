<?php

declare(strict_types=1);

namespace Drupal\stratoserp;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Defines the storage handler class for Stratos entities.
 *
 * This extends the base storage class, adding required special handling for
 * Stratos entities.
 *
 * @ingroup stratoserp
 */
interface StratosStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of revision IDs for a specific entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return int[]
   *   Revision IDs (in ascending order).
   */
  public function revisionIds(EntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(EntityInterface $entity);

  /**
   * Unsets the language for all entities of this type with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
