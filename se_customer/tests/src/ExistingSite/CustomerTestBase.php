<?php

namespace Drupal\Tests\se_customer\ExistingSite;

use Drupal\KernelTests\AssertLegacyTrait;
use Drupal\node\Entity\Node;
use Drupal\Tests\RandomGeneratorTrait;
use Drupal\Tests\UiHelperTrait;
use PHPUnit\Framework\TestCase;
use weitzman\DrupalTestTraits\DrupalTrait;
use Faker\Factory;
use weitzman\DrupalTestTraits\Entity\NodeCreationTrait;
use weitzman\DrupalTestTraits\Entity\TaxonomyCreationTrait;
use weitzman\DrupalTestTraits\Entity\UserCreationTrait;
use weitzman\DrupalTestTraits\GoutteTrait;

abstract class CustomerTestBase extends TestCase
{
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

  /**
   * The base URL.
   *
   * @var string
   */
  protected $baseUrl;

  protected $customer;
  protected $fakerFactory;
  protected $faker;


  protected function setUp()
  {
    parent::setUp();
    $this->setupMinkSession();
    $this->setupDrupal();
    $this->fakerSetup();
  }


  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function tearDown()
  {
    parent::tearDown();
    $this->tearDownDrupal();
    $this->tearDownMinkSession();
  }

  /**
   * Override \Drupal\Tests\UiHelperTrait::prepareRequest since it generates
   * an error, and does nothing useful for DTT. @see https://www.drupal.org/node/2246725.
   */
  protected function prepareRequest()
  {
  }

  protected function fakerSetup() {
    $this->faker = Factory::create();

    $original = error_reporting(0);
    $this->customer->name          = $this->faker->text;
    $this->customer->phoneNumber   = $this->faker->phoneNumber;
    $this->customer->mobileNumber  = $this->faker->phoneNumber;
    $this->customer->streetAddress = $this->faker->streetAddress;
    $this->customer->suburb        = $this->faker->city;
    $this->customer->state         = $this->faker->stateAbbr;
    $this->customer->postcode      = $this->faker->postcode;
    $this->customer->url           = $this->faker->url;
    $this->customer->companyEmail  = $this->faker->companyEmail;
    error_reporting($original);
  }

  protected function setupStaffUser() {
    // Setup user & login
    $staff = $this->createUser([], NULL, FALSE);
    $staff->addRole('staff');
    $staff->save();

    return $staff;
  }

  protected function addCustomer() {

    /** @var Node $node */
    $node = $this->createNode([
      'type' => 'se_customer',
      'title' => $this->customer->name,
      'field_cu_phone' => $this->customer->phoneNumber,
      'field_bu_ref' => ['target_id' => 1],
    ]);
    $this->assertNotEqual($node, FALSE);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);

    $this->assertNotContains('Please fill in this field', $this->getTextContent());

    // Check that what we entered is shown.
    $this->assertContains($this->customer->name, $this->getTextContent());
    $this->assertContains($this->customer->phoneNumber, $this->getTextContent());

    return $node;
  }

}
