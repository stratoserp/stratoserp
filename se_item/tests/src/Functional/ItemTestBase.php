<?php

namespace Drupal\Tests\se_testing\Functional;

use Drupal\Tests\se_testing\Traits\UserCreateTrait;
use Drupal\Tests\se_item\Traits\ItemCreationTrait as StratosItemCreationTrait;

class ItemTestBase extends FunctionalTestBase {

  use StratosItemCreationTrait {
    createItem as stratosCreateItem;
  }

  use UserCreateTrait;

  protected $stockItem;

  protected function setUp() {
    parent::setUp();
  }

  protected function createItem(array $settings = []) {
    $entity = $this->stratosCreateItem($settings);
    $this->markEntityForCleanup($entity);
    return $entity;
  }

}
