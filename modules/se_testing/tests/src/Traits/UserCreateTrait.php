<?php

declare(strict_types=1);

namespace Drupal\Tests\se_testing\Traits;

use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating users during functional tests.
 */
trait UserCreateTrait {

  /**
   * Storage for the faker data for a user.
   *
   * @var \Faker\Factory
   */
  protected $user;

  /**
   * Create a fake user.
   */
  public function userFakerSetup(): void {
    $this->faker = Factory::create();

    $original = error_reporting(0);
    $this->user->name = $this->faker->realText(50);
    error_reporting($original);
  }

  /**
   * Setup a customer, with appropriate role.
   *
   * @return \Drupal\user\Entity\User
   *   The created user.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function setupCustomerUser(): User {
    $this->userFakerSetup();
    return $this->createUserAndCleanup(['customer']);
  }

  /**
   * Setup a staff member, with appropriate role.
   *
   * @return \Drupal\user\Entity\User
   *   The created user.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function setupStaffUser(): User {
    $this->userFakerSetup();
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
   * @return bool|\Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\user\Entity\User
   *   The created user.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createUserAndCleanup(array $roles, array $values = []) {
    $password = user_password();

    $values += [
      'name' => $this->user->name,
      'username' => $this->user->name,
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
