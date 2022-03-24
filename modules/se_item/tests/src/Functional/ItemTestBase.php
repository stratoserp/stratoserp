<?php

declare(strict_types=1);

namespace Drupal\Tests\se_item\Functional;

use Drupal\se_accounting\Service\CurrencyFormat;
use Drupal\Tests\se_item\Traits\ItemTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test Item entity.
 */
class ItemTestBase extends FunctionalTestBase {

  use ItemTestTrait;

  /**
   * Storage for the faker data for item.
   *
   * @var \Faker\Factory
   */
  protected $item;

  /**
   * Storage for the currency formatter.
   *
   * @var \Drupal\se_accounting\Service\CurrencyFormat
   */
  protected CurrencyFormat $currencyFormat;

  /**
   * Setup the basic customer tests.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->itemFakerSetup();
  }

}
