<?php

namespace Drupal\se_payment_line\Plugin\Field\FieldFormatter;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Exception\UndefinedLinkTemplateException;
use Drupal\Core\Field\Annotation\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dynamic_entity_reference\Plugin\Field\FieldFormatter\DynamicEntityReferenceLabelFormatter;

/**
 * Plugin implementation of the 'dynamic entity reference label' formatter.
 *
 * @FieldFormatter(
 *   id = "se_payment_line_formatter",
 *   label = @Translation("Payment line formatter"),
 *   description = @Translation("Payment line formatter"),
 *   field_types = {
 *     "se_payment_line"
 *   }
 * )
 */
class PaymentLineFormatter extends DynamicEntityReferenceLabelFormatter {

  public static function defaultSettings() {
    return [];
  }

  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [];
  }

  public function settingsSummary() {
    return [];
  }

  /**
   * {@inheritdoc}
   *
   * Re-implementation of viewElements from EntityReferenceLabelFormatter
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $host_entity = $items->getEntity();
    $host_type = $host_entity->bundle();

    $list = [];
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      /** @var \Drupal\se_item\Entity\Item|\Drupal\comment\Entity\Comment $entity */
      $uri = $entity->toUrl();

      $element = [
        '#type' => 'link',
        '#title' => $entity->field_it_code->value,
        '#url' => $uri,
        '#options' => $uri->getOptions(),
      ];
      if (!empty($items[$delta]->_attributes)) {
        $element['#options'] += ['attributes' => []];
        $element['#options']['attributes'] += $items[$delta]->_attributes;
        // Unset field item attributes since they have been included in the
        // formatter output and shouldn't be rendered in the field template.
        unset($items[$delta]->_attributes);
      }

      $list[] = [
        '#theme' => 'se_payment_line_formatter',
        '#item' => $element,
        '#quantity' => $items[$delta]->quantity,
        '#price' => \Drupal::service('se_accounting.currency_format')->formatDisplay($items[$delta]->price),
        '#serial' => $items[$delta]->serial,
        '#note' => $items[$delta]->note,
        '#format' => $items[$delta]->format,
      ];
    }

    // Now wrap the lines into a bundle.
    return [
      '#theme' => 'se_payment_lines_formatter',
      '#lines' => $list,
      '#cache' => [
        '#tags' => $host_entity->getCacheTags()
      ]
    ];
  }

}
