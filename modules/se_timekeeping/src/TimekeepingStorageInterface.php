<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_timekeeping\Entity\TimekeepingInterface;

/**
 * Defines the storage handler class for Timekeeping entities.
 *
 * This extends the base storage class, adding required special handling for
 * Timekeeping entities.
 *
 * @ingroup se_timekeeping
 */
interface TimekeepingStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Timekeeping revision IDs for a specific Timekeeping.
   *
   * @param \Drupal\se_timekeeping\Entity\TimekeepingInterface $entity
   *   The Timekeeping entity.
   *
   * @return int[]
   *   Timekeeping revision IDs (in ascending order).
   */
  public function revisionIds(TimekeepingInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Timekeeping author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Timekeeping revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\se_timekeeping\Entity\TimekeepingInterface $entity
   *   The Timekeeping entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(TimekeepingInterface $entity);

  /**
   * Unsets the language for all Timekeeping with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
