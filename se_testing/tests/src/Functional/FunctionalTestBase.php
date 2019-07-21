<?php

namespace Drupal\Tests\se_testing\Functional;

use Drupal\KernelTests\AssertLegacyTrait;
use Drupal\node\Entity\Node;
use Drupal\Tests\RandomGeneratorTrait;
use Drupal\Tests\UiHelperTrait;
use PHPUnit\Framework\TestCase;
use weitzman\DrupalTestTraits\DrupalTrait;
use weitzman\DrupalTestTraits\Entity\NodeCreationTrait;
use weitzman\DrupalTestTraits\Entity\TaxonomyCreationTrait;
use weitzman\DrupalTestTraits\Entity\UserCreationTrait;
use weitzman\DrupalTestTraits\GoutteTrait;

/**
 *
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

  /**
   * The database prefix of this test run.
   *
   * @var string
   */
  protected $databasePrefix;

  protected $fakerFactory;
  protected $faker;

  /**
   *
   */
  protected function setUp() {
    parent::setUp();
    $this->setupMinkSession();
    $this->setupDrupal();
  }

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function tearDown() {
    parent::tearDown();
    $this->tearDownDrupal();
    $this->tearDownMinkSession();
  }

  /**
   * Override \Drupal\Tests\UiHelperTrait::prepareRequest since it generates
   * an error, and does nothing useful for DTT. @see https://www.drupal.org/node/2246725.
   */
  protected function prepareRequest() {
  }

  /**
   * Deleting a node.
   *
   * @param \Drupal\node\Entity\Node $node
   * @param bool $allowed
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function deleteNode(Node $node, bool $allowed) {
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
   * Test standard permissions.
   *
   * @param array $pages
   *   Array of pages to test permissions against.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function basicPermissionCheck(array $pages) {

    $customer = $this->createUser();
    $customer->addRole('customer');
    $customer->save();

    $staff = $this->createUser();
    $staff->addRole('staff');
    $staff->save();

    foreach ($pages as $page) {
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(403);
    }

    foreach ($pages as $page) {
      $this->drupalLogin($customer);
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(403);
      $this->drupalLogout();
    }

    foreach ($pages as $page) {
      $this->drupalLogin($staff);
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(200);
      $this->drupalLogout();
    }

    $customer->delete();
    $staff->delete();
  }

}
