<?php

declare(strict_types=1);

namespace Drupal\Tests\se_ticket\Traits;

use Drupal\se_customer\Entity\Customer;
use Drupal\se_ticket\Entity\Ticket;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait TicketTestTrait {

  protected $ticketName;
  protected $ticketUser;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function ticketFakerSetup(): void {
    $this->faker = Factory::create();

    $this->ticketName = $this->faker->text(45);
  }

  /**
   * Add a ticket and set the customer to the value passed in.
   *
   * @param \Drupal\se_customer\Entity\Customer|null $customer
   *   The customer to associate the ticket with.
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
  public function addTicket(Customer $customer = NULL, bool $allowed = TRUE): ?Ticket {
    if (!isset($this->ticketName)) {
      $this->ticketFakerSetup();
    }

    /** @var \Drupal\se_ticket\Entity\Ticket $ticket */
    $ticket = $this->createTicket([
      'type' => 'se_ticket',
      'name' => $this->ticketName,
      'se_cu_ref' => $customer,
    ]);
    self::assertNotEquals($ticket, FALSE);

    $this->drupalGet($ticket->toUrl());

    $content = $this->getTextContent();

    if (!$allowed) {
      // Equivalent to 403 status.
      self::assertStringContainsString('Access denied', $content);
      return NULL;
    }

    // Equivalent to 200 status.
    self::assertStringContainsString('Skip to main content', $content);
    self::assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    self::assertStringContainsString($this->ticketName, $content);

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
    $this->markEntityForCleanup($ticket);

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
      $this->ticketUser = User::load(\Drupal::currentUser()->id());
      if ($this->ticketUser) {
        $settings['uid'] = $this->ticketUser->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->ticketUser = $this->setUpCurrentUser();
        $settings['uid'] = $this->ticketUser->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Ticket::create($settings);
  }

}
