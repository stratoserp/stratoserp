<?php

declare(strict_types=1);

namespace Drupal\Tests\se_business\Traits;

use Drupal\Core\Entity\EntityInterface;
use Drupal\se_business\Entity\Business;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Provides functions for creating content during functional tests.
 */
trait BusinessTestTrait {

  /**
   * Setup basic faker fields for this test trait.
   */
  public function businessFakerSetup(): void {
    $this->faker = Factory::create();

    $original = error_reporting(0);
    $this->business->name = $this->faker->realText(50);
    $this->business->phoneNumber = $this->faker->phoneNumber;
    $this->business->mobileNumber = $this->faker->phoneNumber;
    $this->business->streetAddress = $this->faker->streetAddress;
    $this->business->suburb = $this->faker->city;
    $this->business->state = $this->faker->stateAbbr;
    $this->business->postcode = $this->faker->postcode;
    $this->business->url = $this->faker->url;
    $this->business->companyEmail = $this->faker->companyEmail;
    error_reporting($original);
  }

  /**
   * Add a Business entity.
   *
   * @todo Provide default Business types and test Customer & Supplier.
   *
   * @param bool $allowed
   *   Whether it should be allowed or not.
   *
   * @return \Drupal\se_business\Entity\Business|null
   *   The created business if successful.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addBusiness(bool $allowed = TRUE): ?Business {
    /** @var \Drupal\se_business\Entity\Business $business */
    $business = $this->createBusiness([
      'type' => 'se_business',
      'name' => $this->business->name,
    ]);
    self::assertNotEquals($business, FALSE);
    $this->drupalGet($business->toUrl());

    if (!$allowed) {
      $this->assertSession()->statusCodeEquals(403);
      return NULL;
    }
    $this->assertSession()->statusCodeEquals(200);

    $content = $this->getTextContent();

    self::assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    self::assertStringContainsString($this->business->name, $content);

    return $business;
  }

  /**
   * Retrieve the business by name.
   *
   * @param string $name
   *   The Business name to retrieve.
   * @param bool $reset
   *   Reset the cache first?
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The found Business Entity.
   */
  public function getBusinessByName(string $name, $reset = FALSE): EntityInterface {
    if ($reset) {
      \Drupal::entityTypeManager()->getStorage('se_business')->resetCache();
    }
    $name = (string) $name;
    $business = \Drupal::entityTypeManager()
      ->getStorage('se_business')
      ->loadByProperties(['name' => $name]);

    return reset($business);
  }

  /**
   * Create and save a Business entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Business entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created Business entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createBusiness(array $settings = []) {
    $business = $this->createBusinessContent($settings);

    $business->save();

    return $business;
  }

  /**
   * Create but dont save a Business entity.
   *
   * @param array $settings
   *   Array of settings to apply to the Business entity.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   *   The created but not yet saved Business entity.
   */
  public function createBusinessContent(array $settings = []) {
    $settings += [
      'type' => 'se_business',
    ];

    if (!array_key_exists('uid', $settings)) {
      $this->business->user = User::load(\Drupal::currentUser()->id());
      if ($this->business->user) {
        $settings['uid'] = $this->business->user->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->business->user = $this->setUpCurrentUser();
        $settings['uid'] = $this->business->user->id();
      }
      else {
        $settings['uid'] = 0;
      }
    }

    return Business::create($settings);
  }

  /**
   * Attempt to delete a business entity.
   *
   * @param \Drupal\se_business\Entity\Business $business
   *   The business to delete.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteBusiness(Business $business) {
    $businessId = $business->id();

    $business->delete();
    \Drupal::entityTypeManager()->getStorage('se_business')->resetCache();

    if (Business::load($businessId)) {
      return TRUE;
    }

    return FALSE;
  }

}
