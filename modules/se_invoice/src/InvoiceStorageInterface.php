<?php

declare(strict_types=1);

namespace Drupal\se_invoice;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\se_invoice\Entity\InvoiceInterface;

/**
 * Defines the storage handler class for Invoice entities.
 *
 * This extends the base storage class, adding required special handling for
 * Invoice entities.
 *
 * @ingroup se_invoice
 */
interface InvoiceStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Invoice revision IDs for a specific Invoice.
   *
   * @param \Drupal\se_invoice\Entity\InvoiceInterface $entity
   *   The Invoice entity.
   *
   * @return int[]
   *   Invoice revision IDs (in ascending order).
   */
  public function revisionIds(InvoiceInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Invoice author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Invoice revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\se_invoice\Entity\InvoiceInterface $entity
   *   The Invoice entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(InvoiceInterface $entity);

  /**
   * Unsets the language for all Invoice with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
