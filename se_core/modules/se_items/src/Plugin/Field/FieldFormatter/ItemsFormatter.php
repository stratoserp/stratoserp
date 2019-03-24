<?php

namespace Drupal\se_items\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Plugin implementation of the 'se_items_default_formatter'.
 *
 * @FieldFormatter(
 *   id = "se_items_default_formatter",
 *   label = @Translation("Items formatter"),
 *   field_types = {
 *     "entity_reference_revisions"
 *   },
 * )
 */
class ItemsFormatter extends EntityReferenceFormatterBase {

//  /**
//   * {@inheritdoc}
//   */
//  public function viewElements(FieldItemListInterface $items, $langcode) {
//    $elements = [];
//    foreach ($items as $delta => $item) {
////      $source = [
////        '#theme' => 'serial_default',
////        '#serial_id' => $item->value,
////      ];
//      $elements[$delta] = [
//        '#markup' => \Drupal::service('renderer')->render(''),
//      ];
//    }
//    return $elements;
//  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      if ($entity->id()) {
        $elements[$delta] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['paragraph-formatter']
          ]
        ];
        $elements[$delta]['info'] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['paragraph-info']
          ]
        ];
        $elements[$delta]['info'] += $entity->getIcons();
        $elements[$delta]['summary'] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['paragraph-summary']
          ]
        ];
        $elements[$delta]['summary']['description'] = [
          '#theme' => 'paragraphs_summary',
          '#summary' => $entity->getSummaryItems(),
        ];
      }
    }
    $elements['#attached']['library'][] = 'paragraphs/drupal.paragraphs.formatter';
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $target_type = $field_definition->getSetting('target_type');
    $paragraph_type = \Drupal::entityTypeManager()->getDefinition($target_type);
    if ($paragraph_type) {
      return $paragraph_type->isSubclassOf(ParagraphInterface::class);
    }

    return FALSE;
  }

}
