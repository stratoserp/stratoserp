<?php

declare(strict_types=1);

namespace Drupal\Tests\se_testing\Traits;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\user\Entity\User;

/**
 *
 */
trait UserCreateTrait {

  /**
   * Setup a customer, with appropriate role.
   *
   * @return User
   */
  public function setupCustomerUser(): User {
    return $this->createUserAndCleanup(['customer']);
  }

  /**
   * Setup a staff member, with appropriate role.
   *
   * @return \Drupal\user\Entity\User
   */
  public function setupStaffUser(): User {
    return $this->createUserAndCleanup(['staff']);
  }

  /**
   * Create a user and add it to the clean up array.
   *
   * @param array $roles
   *   An array of roles to add.
   * @param array $values
   *   An optional array of values to create the user with.
   *
   * @return \Drupal\user\Entity\User|bool
   *   The user.
   */
  protected function createUserAndCleanup(array $roles, array $values = []): User {
    $password = user_password();
    $username = $this->randomString();

    $values += [
      'name' => $username,
      'username' => $username,
      'status' => TRUE,
      'pass' => $password,
    ];
    $values['mail'] = $values['username'] . '@example.com';
    $user = User::create($values);
    $user->passRaw = $password;
    foreach ($roles as $role) {
      $user->addRole($role);
    }
    $user->save();

    $this->cleanupEntities[] = $user;
    return $user;
  }


}
