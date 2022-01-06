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

  protected string $businessName;
  protected string $businessPhoneNumber;
  protected string $businessMobileNumber;
  protected string $businessStreetAddress;
  protected string $businessSuburb;
  protected string $businessState;
  protected string $businessPostcode;
  protected string $businessUrl;
  protected string $businessCompanyEmail;

  /**
   * Setup basic faker fields for this test trait.
   */
  public function businessFakerSetup(): void {
    $this->faker = Factory::create();

    $this->businessName = $this->faker->realText(50);
    $this->businessPhoneNumber = $this->faker->phoneNumber();
    $this->businessMobileNumber = $this->faker->phoneNumber();
    $this->businessStreetAddress = $this->faker->streetAddress();
    $this->businessSuburb = $this->faker->city();
    $this->businessState = $this->faker->stateAbbr();
    $this->businessPostcode = $this->faker->postcode();
    $this->businessUrl = $this->faker->url();
    $this->businessCompanyEmail = $this->faker->companyEmail();
  }

  /**
   * Add a Business entity.
   *
   * @param string $type
   *   The taxonomy type of the business to lookup/create.
   * @param bool $allowed
   *   Whether it should be allowed or not.
   *
   * @return \Drupal\se_business\Entity\Business|null
   *   The created business if successful.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @todo Provide default Business types and test Customer & Supplier.
   */
  public function addBusiness(string $type = 'Customer', bool $allowed = TRUE): ?Business {
    if (!isset($this->businessName)) {
      $this->businessFakerSetup();
    }

    if (!$terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
      'name' => $type,
      'vid' => 'se_business_type',
    ])) {
      return NULL;
    }
    $term = reset($terms);

    /** @var \Drupal\se_business\Entity\Business $business */
    $business = $this->createBusiness([
      'type' => 'se_business',
      'name' => $this->businessName,
      'se_bu_type_ref' => [['target_id' => $term->id()]],
    ]);
    self::assertNotEquals($business, FALSE);
    $this->drupalGet($business->toUrl());

    $content = $this->getTextContent();

    if (!$allowed) {
      // Equivalent to 403 status.
      self::assertStringContainsString('Access denied', $content);
      return NULL;
    }

    // Equivalent to 200 status.
    self::assertStringContainsString('Skip to main content', $content);
    self::assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    self::assertStringContainsString($this->businessName, $content);

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
    $this->markEntityForCleanup($business);

    return $business;
  }

  /**
   * Create but don't save a Business entity.
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
      $this->businessUser = User::load(\Drupal::currentUser()->id());
      if ($this->businessUser) {
        $settings['uid'] = $this->businessUser->id();
      }
      elseif (method_exists($this, 'setUpCurrentUser')) {
        /** @var \Drupal\user\UserInterface $user */
        $this->businessUser = $this->setUpCurrentUser();
        $settings['uid'] = $this->businessUser->id();
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
