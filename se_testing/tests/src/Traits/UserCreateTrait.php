<?php

namespace Drupal\Tests\se_testing\Traits;

trait UserCreateTrait {

  public function setupStaffUser() {
    // Setup user & login
    $staff = $this->createUser([], NULL, FALSE);
    $staff->addRole('staff');
    $staff->save();

    return $staff;
  }
}