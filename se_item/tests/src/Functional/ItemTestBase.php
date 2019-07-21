<?php

namespace Drupal\Tests\se_item\Functional;

use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 *
 */
class ItemTestBase extends FunctionalTestBase {

  use ItemTestTrait;

  protected $item;

  /**
   *
   */
  protected function setUp() {
    parent::setUp();
    $this->itemFakerSetup();
  }

  /**
   *
   */
  public function createItem(array $settings = []) {
    $entity = $this->stratosCreateItem($settings);
    $this->markEntityForCleanup($entity);
    return $entity;
  }

}
