<?php

namespace Drupal\se_customer\Tests;

use Drupal\entity_reference_revisions\Tests\EntityReferenceRevisionsCoreVersionUiTestTrait;
use Drupal\field_ui\Tests\FieldUiTestTrait;
use Drupal\node\Entity\Node;
use Drupal\simpletest\WebTestBase;

/**
 * Tests site configuration.
 *
 * @group se_customer
 */
class CustomerWebTest extends WebTestBase {

  use FieldUiTestTrait;
  use EntityReferenceRevisionsCoreVersionUiTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'node',
    'field',
    'contact',
    'views',
    'taxonomy',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $user = $this->drupalCreateUser(['access stratos erp']);
    $this->drupalLogin($user);
  }

  /**
   * Test site information form.
   */
  public function testCustomerCreation() {
    $title = $this->randomMachineName();
    $phone = $this->randomString(10);
    $edit = array(
      'title[0][value]' => $title,
      'field_cu_phone[0][value]' => $phone,
    );

    $this->drupalPostNodeForm('node/add/se_customer', $edit, t('Save'));
    $this->assertText($title);
    $this->assertText($phone);

    $node = $this->drupalGetNodeByTitle($edit['title[0][value]']);
    $node = Node::load($node->id());
    $node->delete();
  }

}
