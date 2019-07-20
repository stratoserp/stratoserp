<?php

namespace Drupal\se_item\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

class Item {
  use StringTranslationTrait;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  public function findByCode($code) {
     $result = \Drupal::entityQuery('se_item')
        ->condition('status', 1)
        ->condition('field_it_code', $code)
        ->execute();

     return $result;
  }
}
