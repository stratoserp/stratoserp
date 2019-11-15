<?php

declare(strict_types=1);

namespace Drupal\Tests\se_item\Traits;

use Drupal\se_item\Entity\Item;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait ItemTestTrait {

  protected $item;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function itemFakerSetup(): void {
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

  public function createItem(array $settings = []) {
    /** @var \Drupal\se_item\Entity\Item $item */
    $item = $this->createItemContent($settings);
    $item->save();

    $this->markEntityForCleanup($item);

    return $item;
  }

  /**
   *
   */
  public function createItemContent(array $settings = []): Item {
    $settings += [
      'type' => 'se_stock',
    ];

    if (!array_key_exists('uid', $settings)) {
      $this->item->user = User::load(\Drupal::currentUser()->id());
      if ($this->item->user) {
        $settings['uid'] = $this->item->user->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->item->user = $this->setUpCurrentUser();
        $settings['uid'] = $this->item->user->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Item::create($settings);
  }

  /**
   *
   */
  public function getItemByTitle($name, $reset = FALSE) {
    if ($reset) {
      \Drupal::entityTypeManager()->getStorage('se_item')->resetCache();
    }
    $name = (string) $name;
    $items = \Drupal::entityTypeManager()
      ->getStorage('se_item')
      ->loadByProperties(['name' => $name]);

    return reset($items);
  }


}
