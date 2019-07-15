<?php

namespace Drupal\Tests\se_testing\Traits;

use Faker\Factory;
use Drupal\Tests\se_item\Traits\ItemCreationTrait as StratosItemCreationTrait;

/**
 * Provides functions for creating content during functional tests.
 */
trait StockItemTestTrait {

  use StratosItemCreationTrait {
    createItem as stratosCreateItem;
  }

  use UserCreateTrait;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function stockItemFakerSetup() {
    $this->faker = Factory::create();

    $original = error_reporting(0);
    $this->stockItem->name       = $this->faker->realText(20);
    $this->stockItem->code       = $this->faker->word();
    $this->stockItem->serial     = $this->faker->randomNumber(5);
    $this->stockItem->cost_price = $this->faker->numberBetween(5, 10);
    $this->stockItem->sell_price = $this->stockItem->cost_price * 1.2;
    error_reporting($original);
  }

  public function addStockItem() {
    /** @var \Drupal\se_item\Entity\Item $item */
    $item = $this->createItem([
      'type' => 'se_stock',
      'name' => $this->stockItem->name,
    ]);
    $this->assertNotEqual($item, FALSE);
    $this->drupalGet($item->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $this->assertNotContains('Please fill in this field', $this->getTextContent());

    // Check that what we entered is shown.
    $this->assertContains($this->stockItem->name, $this->getTextContent());

    return $item;
  }


}