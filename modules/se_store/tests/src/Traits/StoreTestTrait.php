<?php

declare(strict_types=1);

namespace Drupal\Tests\se_store\Traits;

use Drupal\se_customer\Entity\Customer;
use Drupal\se_store\Entity\Store;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait StoreTestTrait {

  protected $storeName;
  protected $storeUser;
  protected $storePhoneNumber;
  protected $storeMobileNumber;
  protected $storeStreetAddress;
  protected $storeSuburb;
  protected $storeState;
  protected $storePostcode;
  protected $storeUrl;
  protected $storeCompanyEmail;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function storeFakerSetup(): void {
    $this->faker = Factory::create();

    $this->storeName          = $this->faker->realText(50);
    $this->storePhoneNumber   = $this->faker->phoneNumber();
    $this->storeMobileNumber  = $this->faker->phoneNumber();
    $this->storeStreetAddress = $this->faker->streetAddress();
    $this->storeSuburb        = $this->faker->city();
    $this->storeState         = $this->faker->stateAbbr();
    $this->storePostcode      = $this->faker->postcode();
    $this->storeUrl           = $this->faker->url();
    $this->storeEmail         = $this->faker->email;
    $this->storeCompanyEmail  = $this->faker->companyEmail();
  }

  /**
   * Add a store node.
   *
   * @param \Drupal\se_customer\Entity\Customer $customer
   *   The customer to add the store to.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\se_store\Entity\Store|null
   *   The store to return.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addStore(Customer $customer) {
    if (!isset($this->storeName)) {
      $this->storeFakerSetup();
    }

    $store = $this->createStore([
      'type' => 'se_store',
      'name' => $this->storeName,
      'se_phone' => $this->storePhoneNumber,
      'se_cu_ref' => $customer,
    ]);
    self::assertNotEquals($store, FALSE);
    $this->drupalGet($store->toUrl());

    $content = $this->getTextContent();

    // Equivalent to 200 status.
    self::assertStringContainsString('Skip to main content', $content);
    self::assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    self::assertStringContainsString($this->storeName, $content);
    self::assertStringContainsString($this->storePhoneNumber, $content);
    self::assertStringContainsString($customer->getName(), $content);

    return $store;
  }

  /**
   * Add a main store node.
   *
   * @param \Drupal\se_customer\Entity\Customer $customer
   *   The customer to add the store to.
   *
   * @return \Drupal\se_store\Entity\Store
   *   The store to return.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function addMainStore(Customer $customer): Store {
    $config = \Drupal::service('config.factory')->get('se_store.settings');
    $store = $this->addStore($customer);

    $termId = $config->get('main_store_term');
    if ($termId) {
      $store->se_type_ref = $termId;
      $store->save();
      $this->markEntityForCleanup($store);
    }

    return $store;
  }

  /**
   * Create and save a Store entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Store entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created Store entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createStore(array $settings = []) {
    $store = $this->createStoreContent($settings);

    $store->save();
    $this->markEntityForCleanup($store);

    return $store;
  }

  /**
   * Create but dont save a Store entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Store entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created but not yet saved Store entity.
   */
  public function createStoreContent(array $settings = []) {
    $settings += [
      'type' => 'se_store',
    ];

    if (!array_key_exists('uid', $settings)) {
      $this->storeUser = User::load(\Drupal::currentUser()->id());
      if ($this->storeUser) {
        $settings['uid'] = $this->storeUser->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->storeUser = $this->setUpCurrentUser();
        $settings['uid'] = $this->storeUser->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Store::create($settings);
  }

}
