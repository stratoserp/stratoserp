<?php

declare(strict_types=1);

namespace Drupal\Tests\se_item\Functional;

use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;
use Drupal\Tests\se_testing\Traits\UserCreateTrait;

/**
 *
 */
class ItemTestBase extends FunctionalTestBase {

  use ItemTestTrait;
  use UserCreateTrait;

  protected $item;

  /**
   *
   */
  protected function setUp(): void {
    parent::setUp();
    $this->itemFakerSetup();
  }

}
