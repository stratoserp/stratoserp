<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\Functional;

use Behat\Mink\Exception\ExpectationException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Tests\se_testing\Functional\ContactTestBase;
use Drupal\Tests\se_testing\Traits\UserCreateTrait;

/**
 * @coversDefault Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 */
class ContactPermissionsTest extends ContactTestBase {

  use UserCreateTrait;

  protected $customer;
  protected $staff;

  private $pages = [
    'contact_add'  => '/node/add/se_contact',
    'contact_list' => '/se/contact-list',
  ];

  /**
   * Test the basic contact permissions for the default pages.
   */
  public function testContactPermissions(): void {
    $this->basicPermissionCheck($this->pages);
  }

}
