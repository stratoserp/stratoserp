<?php

declare(strict_types=1);

namespace Drupal\Tests\se_item\Traits;

use Drupal\KernelTests\Core\Plugin\FactoryTest;
use Drupal\se_item\Entity\Item;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 *
 */
trait ItemCreationTestTrait {

  public function itemFakerSetup() {
    $this->faker = Factory::create();

    $original                    = error_reporting(0);
    $this->item->name            = $this->faker->realText(20);
    $this->item->code            = $this->faker->word();
    $this->item->serial          = $this->faker->randomNumber(5);
    $this->item->cost_price      = $this->faker->numberBetween(5, 10);
    $this->item->sell_price      = $this->item->cost_price * 1.2;
    error_reporting($original);
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

  /**
   *
   */
  public function createItem(array $settings = []) {
    $item = $this->createItemContent($settings);

    $item->save();

    return $item;
  }

  /**
   *
   */
  public function createItemContent(array $settings = []) {
    $settings += [
      'type' => 'se_stock',
    ];

    if (!array_key_exists('uid', $settings)) {
      $user = User::load(\Drupal::currentUser()->id());
      if ($user) {
        $settings['uid'] = $user->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $user = $this->setUpCurrentUser();
        $settings['uid'] = $user->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Item::create($settings);
  }

}
