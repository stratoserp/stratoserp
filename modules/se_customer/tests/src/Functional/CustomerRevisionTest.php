<?php

declare(strict_types=1);

namespace Drupal\Tests\se_customer\Functional;

/**
 * Test Customer revisions.
 *
 * @coversDefault Drupal\se_customer
 * @group se_customer
 * @group stratoserp
 */
class CustomerRevisionTest extends CustomerTestBase {

  /**
   * Add and edit a customer
   */
  public function testCustomerCreateRevisionStaff(): void {
    $this->drupalLogin($this->staff);
    $testCustomer = $this->addCustomer();
    $this->updateEntity($testCustomer, ['edit-name-0-value' => 'test1']);
    $this->drupalGet($testCustomer->toUrl('canonical'));
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $this->assertNull($page->findLink('Revisions'));

    $this->drupalLogin($this->administrator);
    $this->drupalGet($testCustomer->toUrl('canonical'));
    $this->assertSession()->statusCodeEquals(200);
    $revisions = $page->findLink('Revisions');
    $this->assertNotNull($revisions);
    $revisions->click();
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $this->assertNotNull($page->findLink('Revert'));
    $this->assertNotNull($page->find('css', '.revision-current'));
  }

}
