<?php

namespace Drupal\se_item_line\Plugin\Field\FieldFormatter;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\Exception\UndefinedLinkTemplateException;
use Drupal\Core\Field\Annotation\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\dynamic_entity_reference\Plugin\Field\FieldFormatter\DynamicEntityReferenceLabelFormatter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Render\FilteredMarkup;

/**
 * Plugin implementation of the 'dynamic entity reference label' formatter.
 *
 * @FieldFormatter(
 *   id = "se_item_line_formatter",
 *   label = @Translation("Line item formatter"),
 *   description = @Translation("Line item formatter"),
 *   field_types = {
 *     "se_item_line"
 *   }
 * )
 */
class ItemLineFormatter extends DynamicEntityReferenceLabelFormatter {

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

    $row = [];
    $rows = [];

    $headers = [
      t('Qty'),
      t('Item'),
      t('Price'),
      t('Serial'),
      t('Date'),
      t('Notes'),
    ];

    $cache_tags = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      /** @var \Drupal\se_item\Entity\Item|\Drupal\comment\Entity\Comment $entity */
      $uri = $entity->toUrl();

      unset($item);
      switch ($entity->bundle()) {
        case 'se_timekeeping':
          $item = $entity->field_tk_item->entity->field_it_code->value;
          if ($commented_entity = $entity->getCommentedEntity()) {
            $cache_tags = Cache::mergeTags($cache_tags, $commented_entity->getCacheTags());
          }
          $cache_tags = Cache::mergeTags($cache_tags, $entity->getCacheTags());
          break;
        case 'se_service':
        case 'se_stock':
        case 'se_recurring':
          $item = $entity->field_it_code->value;
          $cache_tags = Cache::mergeTags($cache_tags, $entity->getCacheTags());
          break;
        default:
          \Drupal::logger('ItemLineFormatter')
            ->error('Unhandled item type %type.', ['%type' => $entity->bundle()]);
          continue 2;
          break;
      }

      $element = [
        '#type' => 'link',
        '#title' => $item,
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

      // Transform the notes from the stored value into something
      // safe to display.
      $build = [
        '#type' => 'processed_text',
        '#text' => $items[$delta]->note,
        '#format' => $items[$delta]->format,
        '#filter_types_to_skip' => [],
        '#langcode' => $items[$delta]->getLangcode(),
      ];
      // Capture the cacheability metadata associated with the processed text.
      $processed_text = \Drupal::service('renderer')->renderPlain($build);
      $processed = FilterProcessResult::createFromRenderArray($build)->setProcessedText((string) $processed_text);

      $date = new DrupalDateTime($items[$delta]->completed_date, DateTimeItemInterface::STORAGE_TIMEZONE);
      $display_date = $date->getTimestamp() !== 0 ? gmdate('Y-m-d', $date->getTimestamp()) : '';

      $row = [
        $items[$delta]->quantity,
        render($element),
        \Drupal::service('se_accounting.currency_format')->formatDisplay($items[$delta]->price),
        $items[$delta]->serial,
        $display_date,
        render(FilteredMarkup::create($processed->getProcessedText())),
      ];
      $rows[] = $row;
    }

    // Now wrap the lines into a bundle with cache tags.
    return [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $headers,
      '#cache' => [
        'tags' => $cache_tags,
      ],
    ];
  }

}
