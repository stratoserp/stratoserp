<?php

namespace Drupal\Tests\stratoserp\Functional;

use Drupal\Tests\se_business\Traits\BusinessTestTrait;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Tests the main page search Javascript functionality.
 *
 * @coversDefault Drupal\stratoserp
 * @group stratoserp_core
 * @group stratoserp
 */
class SearchTest extends FunctionalTestBase {

  use BusinessTestTrait;

  /**
   * Tests summary list block admin.
   */
  public function testSearchForBusiness() {
    $page = $this->getSession()->getPage();
    $assert = $this->assertSession();

    $staff = $this->setupStaffUser();
    $this->drupalLogin($staff);
    $testBusiness = $this->addBusiness();

    $this->drupalGet('<front>');

    $page->pressButton('Search');
    $assert->pageTextContains('No search string found');

    $page->fillField('edit-search', $testBusiness->getName() . ' (' . $testBusiness->id() . ')');
    $page->pressButton('Search');
    $assert->pageTextNotContains('No search string found');
    $assert->pageTextContains($testBusiness->getName());

    $this->drupalLogout();
  }

}
