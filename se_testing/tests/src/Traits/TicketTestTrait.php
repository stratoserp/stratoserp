<?php

declare(strict_types=1);

namespace Drupal\Tests\se_testing\Traits;

use Drupal\node\Entity\Node;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait TicketTestTrait {

  protected $ticket;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function ticketFakerSetup(): void {
    $this->faker = Factory::create();

    $original                    = error_reporting(0);
    $this->ticket->name          = $this->faker->text;
    $this->ticket->phoneNumber   = $this->faker->phoneNumber;
    $this->ticket->mobileNumber  = $this->faker->phoneNumber;
    $this->ticket->streetAddress = $this->faker->streetAddress;
    $this->ticket->suburb        = $this->faker->city;
    $this->ticket->state         = $this->faker->stateAbbr;
    $this->ticket->postcode      = $this->faker->postcode;
    $this->ticket->url           = $this->faker->url;
    $this->ticket->companyEmail  = $this->faker->companyEmail;
    $this->ticket->body          = $this->faker->paragraphs(random_int(3, 15));
    error_reporting($original);
  }

  /**
   * Add a ticket and set the customer to the value passed in.
   *
   * @param null $customer
   *
   * @return \Drupal\node\Entity\Node
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function addTicket($customer = NULL): Node {
    $this->ticketFakerSetup();

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->createNode([
      'type' => 'se_ticket',
      'title' => $this->ticket->name,
      'se_bu_ref' => $customer,
    ]);
    $this->assertNotEqual($node, FALSE);
    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    $this->assertNotContains('Please fill in this field', $content);

    // Check that what we entered is shown.
    $this->assertContains($this->ticket->name, $content);

    return $node;
  }

}
