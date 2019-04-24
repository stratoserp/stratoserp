<?php

namespace Drupal\Tests\se_testing\Traits;

use Drupal\node\Entity\Node;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait TicketTestTrait {

  public function ticketFakerSetup() {
    $this->faker = Factory::create();

    $original = error_reporting(0);
    $this->ticket->name          = $this->faker->text;
    $this->ticket->phoneNumber   = $this->faker->phoneNumber;
    $this->ticket->mobileNumber  = $this->faker->phoneNumber;
    $this->ticket->streetAddress = $this->faker->streetAddress;
    $this->ticket->suburb        = $this->faker->city;
    $this->ticket->state         = $this->faker->stateAbbr;
    $this->ticket->postcode      = $this->faker->postcode;
    $this->ticket->url           = $this->faker->url;
    $this->ticket->companyEmail  = $this->faker->companyEmail;
    error_reporting($original);
  }

  public function addTicket() {
    /** @var Node $node */
    $node = $this->createNode([
      'type' => 'se_ticket',
      'title' => $this->ticket->name,
    ]);
    $this->assertNotEqual($node, FALSE);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);

    $this->assertNotContains('Please fill in this field', $this->getTextContent());

    // Check that what we entered is shown.
    $this->assertContains($this->ticket->name, $this->getTextContent());

    return $node;
  }


}