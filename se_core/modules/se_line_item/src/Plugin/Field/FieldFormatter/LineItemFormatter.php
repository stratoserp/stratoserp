<?php

namespace Drupal\se_line_item\Plugin\Field\FieldFormatter;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Field\Annotation\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\dynamic_entity_reference\Plugin\Field\FieldFormatter\DynamicEntityReferenceLabelFormatter;

/**
 * Plugin implementation of the 'dynamic entity reference label' formatter.
 *
 * @FieldFormatter(
 *   id = "se_line_item_formatter",
 *   label = @Translation("Line item formatter"),
 *   description = @Translation("Line item formatter"),
 *   field_types = {
 *     "dynamic_entity_reference"
 *   }
 * )
 */
class LineItemFormatter extends DynamicEntityReferenceLabelFormatter {

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    return $elements;
  }

}
