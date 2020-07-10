<?php

declare(strict_types=1);

namespace Drupal\Tests\se_customer\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * @coversDefault Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 */
class CustomerPermissionsTest extends FunctionalTestBase {

  protected $customer;
  protected $staff;

  private $pages = [
    '/node/add/se_customer',
    '/se/customer-list',
  ];

  /**
   *
   */
  public function testCustomerPermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
