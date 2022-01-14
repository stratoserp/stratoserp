<?php

declare(strict_types=1);

namespace Drupal\Tests\se_testing\Functional;

use Behat\Mink\Exception\ExpectationException;
use Drupal\comment\Entity\Comment;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\Core\Url;
use Drupal\KernelTests\AssertLegacyTrait;
use Drupal\node\Entity\Node;
use Drupal\Tests\se_testing\Traits\UserCreateTestTrait;
use Drupal\Tests\RandomGeneratorTrait;
use Drupal\Tests\UiHelperTrait;
use Drupal\user\Entity\User;
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

  // Include various StratosERP traits.
  use UserCreateTestTrait;

  /**
   * The database prefix of this test run.
   *
   * @var string
   */
  protected $databasePrefix;

  /**
   * Storage for faker.
   *
   * @var \Faker\Factory
   */
  protected $faker;

  /**
   * Customer user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected User $customer;

  /**
   * Staff user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected User $staff;

  /**
   * Owner user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected User $owner;

  /**
   * Setup for the class.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->setupMinkSession();
    $this->setupDrupal();

    $this->customer = $this->setupCustomerUser();
    $this->staff = $this->setupStaffUser();
    $this->owner = $this->setupOwnerUser();
  }

  /**
   * Tear down for the class.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function tearDown(): void {
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
      self::assertNull($link);
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
    if (!$allowed && ($this->getSession()->getStatusCode() === 403)) {
      return;
    }

    $this->assertSession()->statusCodeEquals(200);

    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getCurrentPage();
    $link = $page->find('xpath', '//*[@id="comment-' . $comment->id() . '"]//ul/li/a[contains(text(), \'Delete\')]');

    if (!$allowed) {
      self::assertNull($link);
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
   * Confirm whether user can edit an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to edit.
   * @param bool $allowed
   *   Whether it should be allowed or not.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function editEntity(EntityInterface $entity, bool $allowed = TRUE): void {
    $this->drupalGet($entity->toUrl('edit-form'));
    if (!$allowed && ($this->getSession()->getStatusCode() === 403)) {
      return;
    }

    $this->assertSession()->statusCodeEquals(200);

    $page = $this->getCurrentPage();
    $button = $page->findButton('Save');
    $button->press();
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Confirm whether user can delete an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to delete.
   * @param bool $allowed
   *   Whether it should be allowed or not.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function deleteEntity(EntityInterface $entity, bool $allowed = TRUE): void {
    $this->drupalGet($entity->toUrl('delete-form'));
    if (!$allowed && ($this->getSession()->getStatusCode() === 403)) {
      return;
    }

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

    // Test as anonymous user.
    foreach ($pages as $page) {
      $this->drupalGet($page);
      try {
        $this->assertSession()->statusCodeEquals(403);
      }
      catch (ExpectationException $e) {
        self::fail((string) t('Anon - @page - @message', [
          '@page' => $page,
          '@message' => $e->getMessage(),
        ]));
      }
    }

    // Test as customer user.
    foreach ($pages as $page) {
      $this->drupalLogin($this->customer);
      $this->drupalGet($page);
      try {
        $this->assertSession()->statusCodeEquals(403);
      }
      catch (ExpectationException $e) {
        self::fail((string) t('Business - @page - @message', [
          '@page' => $page,
          '@message' => $e->getMessage(),
        ]));
      }
      $this->drupalLogout();
    }

    // Test as staff.
    foreach ($pages as $page) {
      $this->drupalLogin($this->staff);
      $this->drupalGet($page);
      try {
        $this->assertSession()->statusCodeEquals(200);
      }
      catch (ExpectationException $e) {
        self::fail((string) t('Staff - @page - @message', [
          '@page' => $page,
          '@message' => $e->getMessage(),
        ]));
      }
      $this->drupalLogout();
    }

    // Test as owner.
    foreach ($pages as $page) {
      $this->drupalLogin($this->owner);
      $this->drupalGet($page);
      try {
        $this->assertSession()->statusCodeEquals(200);
      }
      catch (ExpectationException $e) {
        self::fail((string) t('Owner - @page - @message', [
          '@page' => $page,
          '@message' => $e->getMessage(),
        ]));
      }
      $this->drupalLogout();
    }

  }

  /**
   * Logs a user out of the Mink controlled browser and confirms.
   *
   * Confirms logout by checking the login page.
   */
  protected function drupalLogout() {
    // Make a request to the logout page, and redirect to the user page, the
    // idea being if you were properly logged out you should be seeing a login
    // screen.
    $destination = Url::fromRoute('user.page')->toString();
    $this->drupalGet(Url::fromRoute('user.logout', [], ['query' => ['destination' => $destination]]));

    $content = $this->getTextContent();
    self::assertStringContainsString('Log in', $content);

    // @see BrowserTestBase::drupalUserIsLoggedIn()
    unset($this->loggedInUser->sessionId);
    $this->loggedInUser = FALSE;
    \Drupal::currentUser()->setAccount(new AnonymousUserSession());
  }

}
