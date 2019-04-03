<?php

namespace Drupal\Tests\se_invoice\Functional;

use Drupal\Tests\se_testing\Functional\NodeTestBase;

/**
 * @coversDefault Drupal\se_customer
 * @group se_invoice
 * @group stratoserp
 *
 */
class InvoiceCreateTest extends NodeTestBase {

  /**
   * The modules to load to run the test.
   *
   * @var array
   */
  protected static $modules = [
    'comment',
    'field',
    'field_group',
    'field_layout',
    'file',
    'flat_comments',
    'geolocation',
    'layout_discovery',
    'link',
    'menu_ui',
    'node',
    'options',
    'path',
    'se_contact',
    'se_core',
    'se_customer',
    'se_invoice',
    'se_items',
    'se_quote',
    'se_supplier',
    'serial',
    'taxonomy',
    'telephone',
    'text',
    'user',
    'views',
    'views_data_export',
  ];

  private $pages = [
    '/node/add/se_invoice',
  ];

  public function testInvoicePermissions() {
    foreach ($this->pages as $page) {
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(403);
    }

    foreach ($this->pages as $page) {
      $this->drupalLogin($this->customerAccount);
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(403);
      $this->drupalLogout();
    }

    foreach ($this->pages as $page) {
      $this->drupalLogin($this->staffAccount);
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(200);
      $this->drupalLogout();
    }
  }

  public function testAddInvoice($delete = TRUE) {
    $logout = $this->myLogin($this->staffAccount);

    // Create a test customer
    $customer = $this->testCustomerForm();

    // Create a test invoice
    $title = $this->randomString(30);
    $edit = [
      'title[0][value]' => $title,
//      'edit-field-bu-ref-0-target-id' => $customer->id(),
    ];

    $this->drupalPostForm('/node/add/se_invoice', $edit, t('Save'));
    $this->assertSession()->pageTextContains($title);

    $node = $this->drupalGetNodeByTitle($edit['title[0][value]']);

    // If we're going to delete, we need to re-load the node. sqlite hack
    if ($delete) {
      $node->delete();
      $customer->delete();
    }

    if ($logout) {
      $this->drupalLogout();
    }
  }

}