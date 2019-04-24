<?php

namespace Drupal\Tests\se_testing\Traits;

use Drupal\se_item\Entity\Item;
use Drupal\user\Entity\User;

trait ItemCreationTrait {

  public function getItemByTitle($name) {
    $items = \Drupal::entityTypeManager()
      ->getStorage('item')
      ->loadByProperties(['name' => $name]);

    return reset($items);
  }

  protected function createItem(array $settings = []) {
    $settings += [
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

    $item = Item::create($settings);
    $item->save();

    return $item;
  }
}
