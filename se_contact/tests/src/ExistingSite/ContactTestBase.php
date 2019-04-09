<?php

namespace Drupal\Tests\se_contact\ExistingSite;

use Drupal\KernelTests\AssertLegacyTrait;
use Drupal\Tests\RandomGeneratorTrait;
use Drupal\Tests\UiHelperTrait;
use PHPUnit\Framework\TestCase;
use weitzman\DrupalTestTraits\DrupalTrait;
use Faker\Factory;
use weitzman\DrupalTestTraits\Entity\NodeCreationTrait;
use weitzman\DrupalTestTraits\Entity\TaxonomyCreationTrait;
use weitzman\DrupalTestTraits\Entity\UserCreationTrait;
use weitzman\DrupalTestTraits\GoutteTrait;

class ContactTestBase extends TestCase {
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

  protected $contact;
  protected $fakerFactory;
  protected $faker;


  protected function setUp() {
    parent::setUp();
    $this->setupMinkSession();
    $this->setupDrupal();

    $this->faker = Factory::create();

    $original = error_reporting(0);
    $this->contact->name          = $this->faker->text;
    $this->contact->phoneNumber   = $this->faker->phoneNumber;
    $this->contact->mobileNumber  = $this->faker->phoneNumber;
    $this->contact->streetAddress = $this->faker->streetAddress;
    $this->contact->suburb        = $this->faker->city;
    $this->contact->state         = $this->faker->stateAbbr;
    $this->contact->postcode      = $this->faker->postcode;
    $this->contact->url           = $this->faker->url;
    $this->contact->companyEmail  = $this->faker->companyEmail;
    error_reporting($original);

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

}
