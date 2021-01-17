<?php

declare(strict_types=1);

namespace Drupal\Tests\se_testing\Traits;

use Drupal\comment\CommentInterface;
use Drupal\comment\Entity\Comment;
use Drupal\node\Entity\Node;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait TimekeepingTestTrait {

  /**
   * Storage for the faker data for timekeeping.
   *
   * @var \Faker\Factory
   */
  protected $timekeeping;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function timekeepingFakerSetup(): void {
    $this->faker = Factory::create();

    $original                         = error_reporting(0);
    $this->timekeeping->name          = $this->faker->realText(50);
    $this->timekeeping->phoneNumber   = $this->faker->phoneNumber;
    $this->timekeeping->mobileNumber  = $this->faker->phoneNumber;
    $this->timekeeping->streetAddress = $this->faker->streetAddress;
    $this->timekeeping->suburb        = $this->faker->city;
    $this->timekeeping->state         = $this->faker->stateAbbr;
    $this->timekeeping->postcode      = $this->faker->postcode;
    $this->timekeeping->url           = $this->faker->url;
    $this->timekeeping->companyEmail  = $this->faker->companyEmail;
    error_reporting($original);
  }

  /**
   * Add timekeeping to a ticket.
   *
   * @param \Drupal\node\Entity\Node $ticket
   *   The ticket node to attache the timekeeping to.
   *
   * @return \Drupal\comment\Entity\Comment
   *   The returned comment.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function addTimekeeping(Node $ticket): Comment {
    $this->timekeepingFakerSetup();

    $item = \Drupal::service('se_item.service')->findByCode('TECHSERVICE');

    /** @var \Drupal\comment\Entity\Comment $comment */
    $comment = Comment::create([
      'entity_id' => $ticket->id(),
      'entity_type' => 'node',
      'field_name' => 'se_timekeeping',
      'comment_type' => 'se_timekeeping',
      'se_bu_ref' => $ticket->se_bu_ref,
      'se_tk_comment' => $this->timekeeping->name,
      'se_tk_billable' => TRUE,
      'se_tk_billed' => FALSE,
      'se_tk_amount' => 60,
      'se_tk_item' => $item,
      'status' => CommentInterface::PUBLISHED,
    ]);
    $comment->save();
    $this->assertNotEquals($comment, FALSE);
    $this->drupalGet($comment->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    self::assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    self::assertStringContainsString($this->timekeeping->name, $content);

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
