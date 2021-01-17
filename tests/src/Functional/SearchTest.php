<?php

namespace Drupal\Tests\stratoserp\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Tests the main page search Javascript functionality.
 *
 * @coversDefault Drupal\stratoserp
 * @group stratoserp_core
 * @group stratoserp
 */
class SearchTest extends FunctionalTestBase {

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

    $page->pressButton('Search');
    $assert->pageTextContains('No search string found');

    $page->fillField('edit-search', $testCustomer->getTitle() . ' (' . $testCustomer->id() . ')');
    $page->pressButton('Search');
    $assert->pageTextNotContains('No search string found');
    $assert->pageTextContains($testCustomer->getTitle());

    $this->drupalLogout();
  }

}
