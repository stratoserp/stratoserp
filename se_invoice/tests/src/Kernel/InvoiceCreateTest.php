<?php

namespace Drupal\Tests\se_invoice\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Test Description
 * TODO actual/better testing.
 *
 * @group se_invoice
 * @group stratoserp
 */
class InvoiceCreateTest extends KernelTestBase {
  use NodeCreationTrait;
  use UserCreationTrait;

  public static $modules = ['user', 'system', 'field', 'node', 'text', 'filter', 'se_invoice'];

  /**
   * @var \Drupal\user\Entity\UserInterface
   */
  protected $owner;

  protected function setUp() {
    parent::setUp();

    $this->installSchema('system', ['sequences']);
    $this->installSchema('node', ['node_access']);
    $this->installEntitySchema('node');
    $this->installConfig(['field', 'node', 'text', 'filter', 'user']);

    $this->owner = $this->createUser([], 'testuser');
  }

  public function testInvoiceCreation() {
    $nodeTitle = 'Test node!';

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'title' => $nodeTitle,
      'type' => 'page',
      'uid' => $this->owner->id(),
    ]);

    // Assert that the node we created has the title we expect.
    $this->assertEquals($nodeTitle, $node->getTitle());
  }

}