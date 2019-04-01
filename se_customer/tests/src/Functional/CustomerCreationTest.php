<?php

namespace Drupal\Tests\se_customer\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\User;

/**
 * Anonymous user access tests.
 *
 * @coversDefault Drupal\se_customer
 * @group shop8
 */
class CustomerCreationTest extends BrowserTestBase {

  protected $customerAccount;
  protected $staffAccount;

  /**
   * The modules to load to run the test.
   *
   * @var array
   */
  public static $modules = [
    'comment',
    'field',
    'field_group',
    'field_layout',
    'file',
    'geolocation',
    'layout_discovery',
    'link',
    'menu_ui',
    'node',
    'options',
    'path',
    'se_core',
    'se_customer',
    'serial',
    'taxonomy',
    'telephone',
    'text',
    'user',
    'views',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->customerAccount = $this->createTestUser();
    $this->staffAccount = $this->createTestUser(['staff']);
  }

  public function testAddCustomer() {
    $this->drupalGet('node/add/se_customer');
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalLogin($this->customerAccount);
    $this->drupalGet('node/add/se_customer');
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogout();

    $this->drupalLogin($this->staffAccount);
    $this->drupalGet('node/add/se_customer');
    $this->assertSession()->statusCodeEquals(200);
    $this->drupalLogout();
  }

  protected function createTestUser($roles = []) {
    // Generate a random user and password
    $name = $this->randomString();
    $pass = $this->randomString();

    $values = [
      'name' => $name,
      'status' => TRUE,
      'pass' => $pass,
      'mail' => sprintf('%s@test.com', $name),
    ];

    // Create the user and add the passed roles
    $user = User::create($values);
    foreach ($roles as $role) {
      $user->addRole($role);
    }
    $user->passRaw = $pass;
    $user->save();

    return $user;
  }

}