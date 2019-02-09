<?php

namespace Drupal\Tests\se_subscription\Functional;

use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\token\Kernel\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests the subscription auto creation service.
 *
 * @group se_subscription
 * @group stratoserp
 *
 * @coversDefaultClass \Drupal\se_subscription\Service\AutoCreator
 */
class SubscriptionAutoCreatorTest extends KernelTestBase {
  use NodeCreationTrait;
  use UserCreationTrait;

  public static $modules = [
    'user', 'system', 'field', 'node', 'text', 'filter', 'se_invoice', 'se_subscription',
  ];

  protected function setUp() {
    parent::setUp();

    $this->installSchema('system', ['sequences']);
    $this->installSchema('node', ['node_access']);
    $this->installEntitySchema('node');
    $this->installConfig(['field', 'node', 'text', 'filter', 'user']);

    $staff_user = $this->createUser([], 'staffuser');
    $this->createRole([''], 'staff');
    $staff_user->addRole('staff');
    $staff_user->save();
    \Drupal::service('account_switcher')->switchTo($staff_user);

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