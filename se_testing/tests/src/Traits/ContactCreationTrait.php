<?php

namespace Drupal\Tests\se_testing\Traits;

use Drupal\Node\Entity\Node;

/**
 * Provides functions for creating content during functional tests.
 */
trait ContactCreationTrait {

  public function testContactForm() {
    // Setup values to submit with the form.
    $title = $this->randomString(30);
    $phone = $this->randomString(10);
    $edit = [
      'title[0][value]' => $title,
      'field_cu_phone[0][value]' => $phone,
    ];

    // Post the form.
    $this->drupalPostForm('/node/add/se_customer', $edit, t('Save'));

    // Check that what we entered is shown.
    $this->assertSession()->pageTextContains($title);
    $this->assertSession()->pageTextContains($phone);

    /** @var Node $node */
    $node = $this->drupalGetNodeByTitle($edit['title[0][value]']);

    return $node;
  }

}
