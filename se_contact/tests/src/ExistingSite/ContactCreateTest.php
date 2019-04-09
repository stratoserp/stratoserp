<?php

namespace Drupal\Tests\se_contact\ExistingSite;

use Drupal\Tests\se_contact\ExistingSite\ContactTestBase;
use Drupal\node\Entity\Node;

/**
 * @coversDefault Drupal\se_contact
 * @group se_contact
 * @group stratoserp
 *
 */
class ContactCreateTest extends ContactTestBase {

  protected $staff;

  public function testContactAdd() {
    // Setup user & login
    $this->staff = $this->createUser([], NULL, FALSE);
    $this->staff->addRole('staff');
    $this->staff->save();
    $this->drupalLogin($this->staff);

    /** @var Node $node */
    $node = $this->createNode([
      'type' => 'se_contact',
      'title' => $this->contact->name,
      'field_co_phone' => $this->contact->phoneNumber,
      'field_bu_ref' => ['target_id' => 1],
    ]);
    $this->assertNotEqual($node, FALSE);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);

    $this->assertNotContains('Please fill in this field', $this->getTextContent());

    // Check that what we entered is shown.
    $this->assertContains($this->contact->name, $this->getTextContent());
    $this->assertContains($this->contact->phoneNumber, $this->getTextContent());
//    $this->assertContains($customer->name, $this->getTextContent());

    $this->drupalLogout();
  }

}
