<?php

declare(strict_types=1);

namespace Drupal\Tests\se_ticket\Traits;

use Drupal\se_business\Entity\Business;
use Drupal\se_ticket\Entity\Ticket;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait TicketTestTrait {

  /**
   * Setup basic faker fields for this test trait.
   */
  public function ticketFakerSetup(): void {
    $this->faker = Factory::create();

    $original                    = error_reporting(0);
    $this->ticket->name          = $this->faker->text(45);
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
   * Add a ticket and set the business to the value passed in.
   *
   * @param \Drupal\se_business\Entity\Business|null $business
   *   The business to associate the ticket with.
   * @param bool $allowed
   *   Whether it should be allowed or not.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\se_ticket\Entity\Ticket|null
   *   The Ticket to return.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addTicket(Business $business = NULL, bool $allowed = TRUE) {
    if (!isset($this->ticket->name)) {
      $this->ticketFakerSetup();
    }

    /** @var \Drupal\se_ticket\Entity\Ticket $ticket */
    $ticket = $this->createTicket([
      'type' => 'se_ticket',
      'name' => $this->ticket->name,
      'se_bu_ref' => $business,
    ]);
    self::assertNotEquals($ticket, FALSE);

    $this->drupalGet($ticket->toUrl());

    sleep(1);

    if (!$allowed) {
      $this->assertSession()->statusCodeEquals(403);
      return NULL;
    }

    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    self::assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    self::assertStringContainsString($this->ticket->name, $content);

    return $ticket;
  }

  /**
   * Create and save an Ticket entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Ticket entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created Ticket entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createTicket(array $settings = []) {
    $ticket = $this->createTicketContent($settings);

    $ticket->save();

    return $ticket;
  }

  /**
   * Create but dont save a Ticket entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Ticket entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created but not yet saved Ticket entity.
   */
  public function createTicketContent(array $settings = []) {
    $settings += [
      'type' => 'se_ticket',
    ];

    if (!array_key_exists('uid', $settings)) {
      $this->ticket->user = User::load(\Drupal::currentUser()->id());
      if ($this->ticket->user) {
        $settings['uid'] = $this->ticket->user->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->ticket->user = $this->setUpCurrentUser();
        $settings['uid'] = $this->ticket->user->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Ticket::create($settings);
  }

}
