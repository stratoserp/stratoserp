<?php

namespace Drupal\Tests\se_testing\Functional;

use Drupal\KernelTests\AssertLegacyTrait;
use Drupal\Tests\RandomGeneratorTrait;
use Drupal\Tests\se_testing\Traits\SupplierTestTrait;
use Drupal\Tests\se_testing\Traits\UserCreateTrait;
use Drupal\Tests\UiHelperTrait;
use PHPUnit\Framework\TestCase;
use weitzman\DrupalTestTraits\DrupalTrait;
use weitzman\DrupalTestTraits\Entity\NodeCreationTrait;
use weitzman\DrupalTestTraits\Entity\TaxonomyCreationTrait;
use weitzman\DrupalTestTraits\Entity\UserCreationTrait;
use weitzman\DrupalTestTraits\GoutteTrait;

class SupplierTestBase extends TestCase {
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

  // Now our own Traits.
  use SupplierTestTrait;
  use UserCreateTrait;

  /**
   * The database prefix of this test run.
   *
   * @var string
   */
  protected $databasePrefix;

  protected $supplier;
  protected $fakerFactory;
  protected $faker;


  protected function setUp() {
    parent::setUp();
    $this->setupMinkSession();
    $this->setupDrupal();
    $this->supplierFakerSetup();
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
  protected function prepareRequest()
  {
  }

}
