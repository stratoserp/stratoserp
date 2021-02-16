<?php

declare(strict_types=1);

namespace Drupal\se_payment;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_payment\Entity\PaymentInterface;

/**
 * Defines the storage handler class for Payment entities.
 *
 * This extends the base storage class, adding required special handling for
 * Payment entities.
 *
 * @ingroup se_payment
 */
interface PaymentStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Payment revision IDs for a specific Payment.
   *
   * @param \Drupal\se_payment\Entity\PaymentInterface $entity
   *   The Payment entity.
   *
   * @return int[]
   *   Payment revision IDs (in ascending order).
   */
  public function revisionIds(PaymentInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Payment author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Payment revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\se_payment\Entity\PaymentInterface $entity
   *   The Payment entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(PaymentInterface $entity);

  /**
   * Unsets the language for all Payment with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
