<?php

declare(strict_types=1);

namespace Drupal\Tests\se_contact\Traits;

use Drupal\node\Entity\Node;

/**
 * Provides functions for creating content during functional tests.
 */
trait ContactTestTrait {

  /**
   * Add a contact node.
   *
   * @param \Drupal\node\Entity\Node $business
   *   The business to add the contact to.
   *
   * @return \Drupal\node\Entity\Node
   *   The node to return.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function addContact(Node $business): Node {
    $this->contactFakerSetup();

    /** @var \Drupal\node\Entity\Node $contact */
    $contact = $this->createNode([
      'type' => 'se_contact',
      'title' => $this->contact->name,
      'se_co_phone' => $this->contact->phoneNumber,
      'se_bu_ref' => $business,
    ]);
    $this->assertNotEquals($contact, FALSE);
    $this->drupalGet($contact->toUrl());

    sleep(1);

    $content = $this->getTextContent();
    $this->assertStringNotContainsString('Please fill in this field', $content);

    // Check that what we entered is shown.
    $this->assertStringContainsString($this->contact->name, $content);
    $this->assertStringContainsString($this->contact->phoneNumber, $content);
    $this->assertStringContainsString($this->business->name, $content);

    return $contact;
  }

  /**
   * Add a main contact node.
   *
   * @param \Drupal\node\Entity\Node $business
   *   The business to add the contact to.
   *
   * @return \Drupal\node\Entity\Node
   *   The node to return.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function addMainContact(Node $business): Node {
    $config = \Drupal::service('config.factory')->get('se_contact.settings');
    $contact = $this->addContact($business);

    $termId = $config->get('main_contact_term');
    if ($termId) {
      $contact->se_co_type_ref = $termId;
      $contact->save();
    }

    return $contact;
  }

}
