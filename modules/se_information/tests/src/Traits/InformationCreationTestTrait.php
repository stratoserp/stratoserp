<?php

declare(strict_types=1);

namespace Drupal\Tests\se_information\Traits;

use Drupal\se_information\Entity\Information;
use Drupal\user\Entity\User;

/**
 * Test out information manipulation.
 */
trait InformationCreationTestTrait {

  /**
   * Retrieve information by title.
   */
  public function getInformationByTitle($name, $reset = FALSE) {
    if ($reset) {
      \Drupal::entityTypeManager()->getStorage('se_information')->resetCache();
    }
    $name = (string) $name;
    $information = \Drupal::entityTypeManager()
      ->getStorage('se_information')
      ->loadByProperties(['name' => $name]);

    return reset($information);
  }

  /**
   * Create some information content.
   */
  public function createInformation(array $settings = []) {
    $information = $this->createInformationContent($settings);

    $information->save();
    $this->markEntityForCleanup($information);

    return $information;
  }

  /**
   * Create some information content.
   */
  public function createInformationContent(array $settings = []) {
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

    return Information::create($settings);
  }

}
