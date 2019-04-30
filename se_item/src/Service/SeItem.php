<?php

namespace Drupal\se_item\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

class SeItem {
  use StringTranslationTrait;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  public function findByCode($code) {
     $result = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'se_item')
        ->condition('field_it_code', $code)
        ->execute();

     return $result;
  }
}
