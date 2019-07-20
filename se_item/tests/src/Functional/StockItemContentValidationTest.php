<?php

namespace Drupal\Tests\se_item\Functional;

use Drupal\Tests\se_testing\Functional\ItemTestBase;

class StockItemContentValidationTest extends ItemTestBase  {

  /**
   * Tests the block content validation constraints.
   */
  public function testValidation() {
    // Add a block.
    $description = $this->randomMachineName();
    $block = $this->createBlockContent($description, 'basic');
    // Validate the block.
    $violations = $block->validate();
    // Make sure we have no violations.
    $this->assertEqual(count($violations), 0);
    // Save the block.
    $block->save();

    // Add another block with the same description.
    $block = $this->createBlockContent($description, 'basic');
    // Validate this block.
    $violations = $block->validate();
    // Make sure we have 1 violation.
    $this->assertEqual(count($violations), 1);
    // Make sure the violation is on the info property
    $this->assertEqual($violations[0]->getPropertyPath(), 'info');
    // Make sure the message is correct.
    $this->assertEqual($violations[0]->getMessage(), format_string('A custom block with block description %value already exists.', [
      '%value' => $block->label(),
    ]));
  }

}
