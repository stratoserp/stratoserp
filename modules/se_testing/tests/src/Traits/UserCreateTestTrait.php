<?php

declare(strict_types=1);

namespace Drupal\Tests\se_testing\Traits;

use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating users during functional tests.
 */
trait UserCreateTestTrait {

  /**
   * Storage for the faker data for a user.
   *
   * @var \Faker\Factory
   */
  protected $userName;

  /**
   * Create a fake user.
   */
  public function userFakerSetup(): void {
    $this->faker = Factory::create();

    $this->userName = $this->faker->realText(50);
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
   * Setup a higher level user, with appropriate role.
   *
   * @return \Drupal\user\Entity\User
   *   The created user.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function setupOwnerUser(): User {
    $this->userFakerSetup();
    return $this->createUserAndCleanup(['owner']);
  }

  /**
   * Setup a developer level user, with appropriate role.
   *
   * @return \Drupal\user\Entity\User
   *   The created user.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function setupAdministratorUser(): User {
    $this->userFakerSetup();
    return $this->createUserAndCleanup(['administrator']);
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
    $password = \Drupal::service('password_generator')->generate();

    $values += [
      'name' => $this->userName,
      'username' => $this->userName,
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
