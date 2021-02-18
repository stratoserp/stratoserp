<?php

declare(strict_types=1);

namespace Drupal\Tests\se_information\Functional;

use Drupal\Tests\se_information\Traits\InformationTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Perform basic information tests.
 */
class InformationTestBase extends FunctionalTestBase {

  use InformationTestTrait;

  /**
   * Faker factory for information.
   *
   * @var \Faker\Factory
   */
  protected $information;

  /**
   * Setup the basic information tests.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->informationFakerSetup();
  }

  /**
   * Create an information item.
   */
  public function createItem(array $settings = []) {
    $entity = $this->stratosCreateInformation($settings);
    $this->markEntityForCleanup($entity);
    return $entity;
  }

}
