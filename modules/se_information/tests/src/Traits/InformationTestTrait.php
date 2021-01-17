<?php

declare(strict_types=1);

namespace Drupal\Tests\se_information\Traits;

use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait InformationTestTrait {

  use InformationCreationTrait {
    createInformation as stratosCreateInformation;
    createInformationContent as stratosCreateInformationContent;
  }

  /**
   * Setup basic faker fields for this test trait.
   */
  public function informationFakerSetup() {
    $this->faker = Factory::create();

    $original                = error_reporting(0);
    $this->information->name = $this->faker->realText(20);
    error_reporting($original);
  }

  /**
   * Add an information entity.
   */
  public function addInformation($type) {
    /** @var \Drupal\se_information\Entity\Information $information */
    $information = $this->createInformation([
      'type' => $type,
      'name' => $this->information->name,
      'se_it_code' => $this->information->code,
    ]);
    $this->assertNotEquals($information, FALSE);
    $this->drupalGet($information->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    $this->assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    $this->assertStringContainsString($this->information->name, $content);

    return $information;
  }

}
