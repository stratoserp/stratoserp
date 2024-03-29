<?php

declare(strict_types=1);

namespace Drupal\se_item\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Various Item related functions.
 *
 * @package Drupal\se_item\Service
 */
class ItemService {
  use StringTranslationTrait;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Item constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entitymanager for injection.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Find an item by its code.
   *
   * @param string $code
   *   Item code to lookup.
   *
   * @return array|int
   *   Found items.
   */
  public function findByCode($code) {
    return \Drupal::entityQuery('se_item')
      ->accessCheck(TRUE)
      ->condition('se_code', $code)
      ->execute();
  }

}
