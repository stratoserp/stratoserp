<?php

declare(strict_types=1);

namespace Drupal\Tests\se_testing\Functional;

use Behat\Mink\Exception\ExpectationException;
use Drupal\comment\Entity\Comment;
use Drupal\KernelTests\AssertLegacyTrait;
use Drupal\node\Entity\Node;
use Drupal\Tests\se_testing\Traits\ContactTestTrait;
use Drupal\Tests\se_testing\Traits\CustomerTestTrait;
use Drupal\Tests\se_testing\Traits\GoodsReceiptTestTrait;
use Drupal\Tests\se_testing\Traits\InvoiceTestTrait;
use Drupal\Tests\se_testing\Traits\QuoteTestTrait;
use Drupal\Tests\se_testing\Traits\SupplierTestTrait;
use Drupal\Tests\se_testing\Traits\TicketTestTrait;
use Drupal\Tests\se_testing\Traits\TimekeepingTestTrait;
use Drupal\Tests\se_testing\Traits\UserCreateTrait;
use Drupal\Tests\RandomGeneratorTrait;
use Drupal\Tests\UiHelperTrait;
use PHPUnit\Framework\TestCase;
use weitzman\DrupalTestTraits\DrupalTrait;
use weitzman\DrupalTestTraits\Entity\NodeCreationTrait;
use weitzman\DrupalTestTraits\Entity\TaxonomyCreationTrait;
use weitzman\DrupalTestTraits\Entity\UserCreationTrait;
use weitzman\DrupalTestTraits\GoutteTrait;

/**
 * Our own functional test base class.
 */
class FunctionalTestBase extends TestCase {
  use DrupalTrait;
  use GoutteTrait;
  use NodeCreationTrait;
  use UserCreationTrait;
  use TaxonomyCreationTrait;
  use UiHelperTrait;

  // The entity creation traits need this.
  use RandomGeneratorTrait;

  // Core is still using this in role creation, so it must be included here when
  // using the UserCreationTrait.
  use AssertLegacyTrait;

  use ContactTestTrait;
  use CustomerTestTrait;
  use GoodsReceiptTestTrait;
  use InvoiceTestTrait;
  use QuoteTestTrait;
  use TicketTestTrait;
  use TimekeepingTestTrait;
  use SupplierTestTrait;
  use UserCreateTrait;

  /**
   * The database prefix of this test run.
   *
   * @var string
   */
  protected $databasePrefix;

  /**
   * Storage for faker factory.
   *
   * @var FakerFactory
   */
  protected $fakerFactory;

  /**
   * Storage for faker.
   *
   * @var FakerFactory
   */
  protected $faker;

  /**
   * Setup for the class.
   */
  protected function setUp() {
    parent::setUp();
    $this->setupMinkSession();
    $this->setupDrupal();
  }

  /**
   * Tear down for the class.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function tearDown() {
    parent::tearDown();
    $this->tearDownDrupal();
    $this->tearDownMinkSession();
  }

  /**
   * Override to remove error.
   *
   * Override \Drupal\Tests\UiHelperTrait::prepareRequest since it generates
   * an error, and does nothing useful for DTT.
   *
   * @see https://www.drupal.org/node/2246725
   */
  protected function prepareRequest() {
  }

  /**
   * Confirm whether user can delete a node.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node to delete.
   * @param bool $allowed
   *   Whether it should be allowed or not.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function deleteNode(Node $node, bool $allowed): void {
    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getCurrentPage();
    $link = $page->find('xpath', '//nav/ul/li/a[contains(text(), \'Delete\')]');

    if (!$allowed) {
      $this->assertNull($link);
      return;
    }

    $link->click();
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $button = $page->findButton('Delete');
    $button->press();
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Confirm whether user can delete a comment.
   *
   * @param \Drupal\comment\Entity\Comment $comment
   *   The comment to delete.
   * @param bool $allowed
   *   Whether it should be allowed or not.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function deleteComment(Comment $comment, bool $allowed): void {
    $this->drupalGet($comment->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getCurrentPage();
    $link = $page->find('xpath', '//*[@id="comment-' . $comment->id() . '"]//ul/li/a[contains(text(), \'Delete\')]');

    if (!$allowed) {
      $this->assertNull($link);
      return;
    }

    $link->click();
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $button = $page->findButton('Delete');
    $button->press();
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Test standard permissions.
   *
   * @param array $pages
   *   Array of pages to test permissions against.
   */
  public function basicPermissionCheck(array $pages): void {

    // Can't test anon 403 if r4032login is present.
    if (!\Drupal::moduleHandler()->moduleExists('r4032login')) {
      foreach ($pages as $page) {
        $this->drupalGet($page);
        try {
          $this->assertSession()->statusCodeEquals(403);
        }
        catch (ExpectationException $e) {
          $this->fail((string) t('Anon - @page - @message', ['@page' => $page, '@message' => $e->getMessage()]));
        }
      }
    }

    $customer = $this->setupCustomerUser();
    $staff = $this->setupStaffUser();

    foreach ($pages as $page) {
      $this->drupalLogin($customer);
      $this->drupalGet($page);
      try {
        $this->assertSession()->statusCodeEquals(403);
      }
      catch (ExpectationException $e) {
        $this->fail((string) t('Customer - @page - @message', ['@page' => $page, '@message' => $e->getMessage()]));
      }
      $this->drupalLogout();
    }

    foreach ($pages as $page) {
      $this->drupalLogin($staff);
      $this->drupalGet($page);
      try {
        $this->assertSession()->statusCodeEquals(200);
      }
      catch (ExpectationException $e) {
        $this->fail((string) t('Staff - @page - @message', ['@page' => $page, '@message' => $e->getMessage()]));
      }
      $this->drupalLogout();
    }
  }

}
