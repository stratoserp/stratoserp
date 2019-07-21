<?php

namespace Drupal\Tests\se_item\Traits;

use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait ItemTestTrait {

  use ItemCreationTestTrait {
    createItem as stratosCreateItem;
    createItemContent as stratosCreateItemContent;
  }

  /**
   * Setup basic faker fields for this test trait.
   */
  public function itemFakerSetup() {
    $this->faker = Factory::create();

    $original               = error_reporting(0);
    $this->item->name       = $this->faker->realText(20);
    $this->item->code       = $this->faker->word();
    $this->item->serial     = $this->faker->randomNumber(5);
    $this->item->cost_price = $this->faker->numberBetween(5, 10);
    $this->item->sell_price = $this->item->cost_price * 1.2;
    error_reporting($original);
  }

  /**
   *
   */
  public function addItem($type) {
    /** @var \Drupal\se_item\Entity\Item $item */
    $item = $this->createItem([
      'type' => $type,
      'name' => $this->item->name,
      'field_it_code' => $this->item->code,
    ]);
    $this->assertNotEqual($item, FALSE);
    $this->drupalGet($item->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    $this->assertNotContains('Please fill in this field', $content);

    // Check that what we entered is shown.
    $this->assertContains($this->item->name, $content);

    return $item;
  }

}
