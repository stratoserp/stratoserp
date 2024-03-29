<?php

declare(strict_types=1);

namespace Drupal\Tests\stratoserp\Functional;

use Drupal\Tests\se_customer\Traits\CustomerTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Tests the main page search Javascript functionality.
 *
 * @covers \Drupal\stratoserp\Controller\AutocompleteController
 * @uses \Drupal\stratoserp\Form\SearchForm
 * @group stratoserp_core
 * @group stratoserp
 */
class SearchTest extends FunctionalTestBase {

  use CustomerTestTrait;

  /**
   * Tests summary list block admin.
   */
  public function testSearchForCustomer() {
    $page = $this->getSession()->getPage();
    $assert = $this->assertSession();

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $testCustomer = $this->addCustomer();

    $this->drupalGet('<front>');

    $page->pressButton('Load');
    $assert->pageTextContains('No search string found');

    $page->fillField('edit-search', $testCustomer->getName() . ' (' . $testCustomer->id() . ')');
    $page->pressButton('Load');
    $assert->pageTextNotContains('No search string found');
    $assert->pageTextContains($testCustomer->getName());

    $this->drupalLogout();
  }

}
