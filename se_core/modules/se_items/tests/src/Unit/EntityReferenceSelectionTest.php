<?php

namespace Drupal\Tests\se_items\Unit;

use Drupal\Tests\UnitTestCase;

class EntityReferenceSelectionTest extends UnitTestCase {
  public function setUp() {
    parent::setUp();
    $container = new ContainerBuilder();
    $container->set('string_translation', $this->getStringTranslationStub());
    \Drupal::setContainer($container);
  }

}
