<?php

namespace Drupal\Tests\se_contact\ExistingSiteWebDriver;

use Drupal\node\Entity\Node;
use weitzman\DrupalTestTraits\ExistingSiteSelenium2DriverTestBase;
use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * @coversDefault Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 *
 */
class ContactCreateTest extends ExistingSiteWebDriverTestBase {

  protected $staff;

  public function testContactAdd() {
    $this->staff = $this->createUser([], NULL, FALSE);
    $this->staff->addRole('staff');
    $this->staff->save();

    // Setup values to submit with the form.
    $title = $this->randomString(30);
    $phone = $this->randomString(10);

    /** @var Node $node */
    $this->createNode([
      'type' => 'se_contact',
      'title' => $title,
      'field_cu_phone' => $phone,
    ]);

    // Check that what we entered is shown.
//    $this->assertSession()->pageTextContains($title);
    $this->assertSession()->pageTextContains($phone);

    $this->drupalLogout();
  }

}
