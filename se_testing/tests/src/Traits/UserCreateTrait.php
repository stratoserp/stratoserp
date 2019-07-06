<?php

namespace Drupal\Tests\se_testing\Traits;

trait UserCreateTrait {

  /**
   * Setup a customer, with appropriate role.
   *
   * @return mixed
   */
  public function setupCustomerUser() {
    // Setup user & login
    $staff = $this->createUser([], NULL, FALSE);
    $staff->addRole('customer');
    $staff->save();

    return $staff;
  }

  /**
   * Setup a staff member, with appropriate role.
   *
   * @return mixed
   */
  public function setupStaffUser() {
    // Setup user & login
    $staff = $this->createUser([], NULL, FALSE);
    $staff->addRole('staff');
    $staff->save();

    return $staff;
  }


}