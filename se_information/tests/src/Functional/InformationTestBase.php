<?php

namespace Drupal\Tests\se_information\Functional;

use Drupal\Tests\se_information\Traits\InformationTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

class InformationTestBase extends FunctionalTestBase {

  use InformationTestTrait;

  protected $information;

  protected function setUp() {
    parent::setUp();
    $this->informationFakerSetup();
  }

  public function createItem(array $settings = []) {
    $entity = $this->stratosCreateInformation($settings);
    $this->markEntityForCleanup($entity);
    return $entity;
  }

}
