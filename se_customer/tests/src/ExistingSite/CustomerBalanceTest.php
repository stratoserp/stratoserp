<?php

namespace Drupal\Tests\se_customer\ExistingSite;

use Drupal\node\Entity\Node;
use Drupal\se_testing\Traits\CustomerCreateTrait;


/**
 * @coversDefault Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 *
 */
class CustomerCreateTest extends CustomerTestBase {

  use CustomerCreateTrait;

  protected $staff;

  public function testCustomerAdd() {

    // Setup user & login
    $this->staff = $this->createUser([], NULL, FALSE);
    $this->staff->addRole('staff');
    $this->staff->save();
    $this->drupalLogin($this->staff);

    /** @var Node $node */
    $node = $this->createNode([
      'type' => 'se_customer',
      'title' => $this->customer->name,
      'field_cu_phone' => $this->customer->phoneNumber,
      'field_bu_ref' => ['target_id' => 1],
    ]);
    $this->assertNotEqual($node, FALSE);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);

    $this->assertNotContains('Please fill in this field', $this->getTextContent());

    // Check that what we entered is shown.
    $this->assertContains($this->customer->name, $this->getTextContent());
    $this->assertContains($this->customer->phoneNumber, $this->getTextContent());

    $this->drupalLogout();
  }

}