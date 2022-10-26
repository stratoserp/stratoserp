<?php

declare(strict_types=1);

namespace Drupal\Tests\se_store\FunctionalJavascript;

use Drupal\Tests\se_customer\Traits\CustomerTestTrait;
use Drupal\Tests\se_store\Traits\StoreTestTrait;
use Drupal\Tests\se_testing\FunctionalJavascript\FunctionalJavascriptTestBase;

/**
 * Test Store autocomplete.
 *
 * @covers \Drupal\se_store
 * @group se_store
 * @group stratoserp
 */
class StoreAutocompleteTest extends FunctionalJavascriptTestBase {

  use CustomerTestTrait;
  use StoreTestTrait;

  /**
   * Test store search.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testStoreSearch(): void {

    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $this->addStore($testCustomer);
    $this->drupalLogout();

    // Make a new staff user and test they can find the store.
    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $this->drupalGet('<front>');

    $this->getCurrentPage();
  }

}
