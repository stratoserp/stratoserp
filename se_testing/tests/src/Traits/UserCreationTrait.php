<?php

namespace Drupal\Tests\se_testing\Traits;

use Drupal\user\Entity\User;

trait UserCreationTrait {
  protected $loggedInUser;
  protected $customerAccount;
  protected $staffAccount;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->customerAccount = $this->createTestUser();
    $this->staffAccount = $this->createTestUser(['staff']);
  }

  protected function createTestUser($roles = []) {
    // Generate a random user and password
    $name = $this->randomString();
    $pass = $this->randomString();

    $values = [
      'name' => $name,
      'status' => TRUE,
      'pass' => $pass,
      'mail' => sprintf('%s@test.com', $name),
    ];

    // Create the user and add the passed roles
    $user = User::create($values);
    foreach ($roles as $role) {
      $user->addRole($role);
    }
    $user->passRaw = $pass;
    $user->save();

    return $user;
  }

  protected function myLogin(User $account) {
    if (!$this->getLoggedInUser()) {
      $this->drupalLogin($account);
      return TRUE;
    }
    return FALSE;
  }

  protected function getLoggedInUser() {
    return $this->loggedInUser;
  }

  protected function setLoggedInUser($state = TRUE) {
    $this->loggedInUser = $state;
  }

}