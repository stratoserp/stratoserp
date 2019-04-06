<?php

namespace Drupal\Tests\se_customer\ExistingSiteWebDriver;

use Drupal\node\Entity\Node;
use weitzman\DrupalTestTraits\ExistingSiteBase;
use weitzman\DrupalTestTraits\ExistingSiteSelenium2DriverTestBase;
use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * @coversDefault Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 *
 */
class CustomerCreateTest extends ExistingSiteWebDriverTestBase {

  protected $staff;

  public function testCustomerAdd($delete = TRUE) {

    $this->staff = $this->createUser([], NULL, FALSE);
    $this->staff->addRole('staff');
    $this->staff->save();

    // Setup values to submit with the form.
    $title = $this->randomString(30);
    $phone = $this->randomString(10);

    /** @var Node $node */
    $this->createNode([
      'type' => 'se_customer',
      'title' => $title,
//      'cu_phone' => $phone,
    ]);

    // Check that what we entered is shown.
    $this->assertSession()->pageTextContains($title);
//    $this->assertSession()->pageTextContains($phone);

    $this->drupalLogout();
  }

}