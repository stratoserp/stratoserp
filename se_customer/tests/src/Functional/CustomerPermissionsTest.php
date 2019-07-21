<?php

namespace Drupal\Tests\se_customer\Functional;

use Drupal\Tests\se_testing\Functional\CustomerTestBase;

/**
 * @coversDefault Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 */
class CustomerPermissionsTest extends CustomerTestBase {

  protected $customer;
  protected $staff;

  private $pages = [
    '/node/add/se_customer',
  ];

  /**
   *
   */
  public function testCustomerPermissions() {

    $this->basicPermissionCheck($this->pages);

  }

}
