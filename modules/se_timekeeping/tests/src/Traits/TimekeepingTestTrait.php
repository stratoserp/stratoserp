<?php

declare(strict_types=1);

namespace Drupal\Tests\se_timekeeping\Traits;

use Drupal\comment\CommentInterface;
use Drupal\comment\Entity\Comment;
use Drupal\se_ticket\Entity\Ticket;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait TimekeepingTestTrait {

  protected $timekeepingName;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function timekeepingFakerSetup(): void {
    $this->faker = Factory::create();

    $this->timekeepingName          = $this->faker->realText(45);
  }

  /**
   * Add timekeeping to a ticket.
   *
   * @param \Drupal\se_ticket\Entity\Ticket $ticket
   *   The business to associate the ticket with.
   * @param bool $allowed
   *   Whether it should be allowed or not.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function addTimekeeping(Ticket $ticket, bool $allowed = TRUE) {
    if (!isset($this->timekeepingName)) {
      $this->timekeepingFakerSetup();
    }

    $item = \Drupal::service('se_item.service')->findByCode('TECHSERVICE');

    /** @var \Drupal\comment\Entity\Comment $comment */
    $comment = Comment::create([
      'entity_id' => $ticket->id(),
      'entity_type' => 'se_ticket',
      'field_name' => 'se_timekeeping',
      'comment_type' => 'se_timekeeping',
      'se_bu_ref' => $ticket->se_bu_ref,
      'se_tk_comment' => $this->timekeepingName,
      'se_tk_billable' => TRUE,
      'se_tk_billed' => FALSE,
      'se_tk_amount' => 60,
      'se_tk_item' => $item,
      'status' => CommentInterface::PUBLISHED,
    ]);
    $comment->save();
    self::assertNotEquals($comment, FALSE);

    $this->drupalGet($comment->toUrl());

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
    self::assertStringContainsString($this->timekeepingName, $content);

    return $comment;
  }

  /**
   * Test deleting a timekeeping entry.
   *
   * @param \Drupal\comment\Entity\Comment $comment
   *   The timekeeping comment to try and delete.
   * @param bool $allowed
   *   Whether the deletion shoulw be allowed.
   */
  public function deleteTimekeeping(Comment $comment, bool $allowed): void {
    $this->deleteComment($comment, $allowed);
  }

}
