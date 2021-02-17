<?php

declare(strict_types=1);

namespace Drupal\Tests\se_document\Functional;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Information document permission tests.
 *
 * @coversDefault Drupal\se_document
 * @group se_document
 * @group stratoserp
 */
class InformationDocumentCreateTest extends ExistingSiteBase {

  /**
   * Faker factory for business.
   *
   * @var \Faker\Factory
   */
  protected $business;

  /**
   * Faker factory for staff.
   *
   * @var \Faker\Factory
   */
  protected $staff;

  /**
   * List of pages to test permissions on.
   *
   * @var string[]
   */
  private $pages = [
    '/information/add/se_document',
  ];

  /**
   * Test the information document permissions.
   */
  public function testInformationDocumentPermissions() {
    $this->business = $this->createUser([], NULL, FALSE);
    $this->business->addRole('business');
    $this->business->save();

    $this->staff = $this->createUser([], NULL, FALSE);
    $this->staff->addRole('staff');
    $this->staff->save();

    foreach ($this->pages as $page) {
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(403);
    }

    foreach ($this->pages as $page) {
      $this->drupalLogin($this->business);
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(403);
      $this->drupalLogout();
    }

    foreach ($this->pages as $page) {
      $this->drupalLogin($this->staff);
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(200);
      $this->drupalLogout();
    }
  }

}
