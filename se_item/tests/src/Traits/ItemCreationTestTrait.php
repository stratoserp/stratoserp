<?php

namespace Drupal\Tests\se_item\Traits;

use Drupal\se_item\Entity\Item;
use Drupal\user\Entity\User;

trait ItemCreationTestTrait {

  public function getItemByTitle($name, $reset = FALSE) {
    if ($reset)  {
      \Drupal::entityTypeManager()->getStorage('se_item')->resetCache();
    }
    $name = (string) $name;
    $items = \Drupal::entityTypeManager()
      ->getStorage('se_item')
      ->loadByProperties(['name' => $name]);

    return reset($items);
  }

  public function createItem(array $settings = []) {
    $item = $this->createItemContent($settings);

    $item->save();

    return $item;
  }

  public function createItemContent(array $settings = []) {
    $settings += [
      'type' => 'se_stock'
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