<?php

namespace Drupal\Tests\se_testing\Traits;

use Drupal\se_item\Entity\Item;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait StockItemTestTrait {

  public function stockItemFakerSetup() {
    $this->faker = Factory::create();

    $original = error_reporting(0);
    $this->stockItem->name       = $this->faker->realText(20);
    $this->stockItem->code       = $this->faker->word();
    $this->stockItem->serial     = $this->faker->randomNumber(5);
    $this->stockItem->cost_price = $this->faker->numberBetween(5, 10);
    $this->stockItem->sell_price = $this->cost_price * 1.2;
    error_reporting($original);
  }

  public function addStockItem() {
    /** @var \Drupal\se_item\Entity\Item $item */
    $item = $this->createNode([
      'type' => 'se_stock',
      'title' => $this->stockItem->name,
    ]);
    $this->assertNotEqual($item, FALSE);
    $this->drupalGet('item/' . $item->id());
    $this->assertSession()->statusCodeEquals(200);

    $this->assertNotContains('Please fill in this field', $this->getTextContent());

    // Check that what we entered is shown.
    $this->assertContains($this->stockItem->name, $this->getTextContent());

    return $item;
  }


}