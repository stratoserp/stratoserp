<?php

namespace Drupal\Tests\se_testing\Traits;

use Drupal\se_item\Entity\Item;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait ItemTestTrait {

  /**
   * Setup basic faker fields for this test trait.
   */
  public function itemFakerSetup() {
    $this->faker = Factory::create();

    $original = error_reporting(0);
    $this->item->name       = $this->faker->realText(20);
    $this->item->code       = $this->faker->word();
    $this->item->serial     = $this->faker->randomNumber(5);
    $this->item->cost_price = $this->faker->numberBetween(5, 10);
    $this->item->sell_price = $this->item->cost_price * 1.2;
    error_reporting($original);
  }

  public function addItem($type) {
    /** @var \Drupal\se_item\Entity\Item $item */
    $item = $this->createItem([
      'type' => $type,
      'title' => $this->item->name,
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