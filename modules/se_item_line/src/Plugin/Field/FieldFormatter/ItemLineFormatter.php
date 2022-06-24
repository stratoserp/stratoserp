<?php

declare(strict_types=1);

namespace Drupal\se_item_line\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\dynamic_entity_reference\Plugin\Field\FieldFormatter\DynamicEntityReferenceLabelFormatter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Render\FilteredMarkup;
use Drupal\se_timekeeping\Entity\Timekeeping;

/**
 * Plugin implementation of the 'dynamic entity reference label' formatter.
 *
 * @FieldFormatter(
 *   id = "se_item_line_formatter",
 *   label = @Translation("Item line formatter"),
 *   description = @Translation("Item line formatter"),
 *   field_types = {
 *     "se_item_line"
 *   }
 * )
 */
class ItemLineFormatter extends DynamicEntityReferenceLabelFormatter {

  /**
   * Remove default settings.
   */
  public static function defaultSettings() {
    return [];
  }

  /**
   * Remove default settings form.
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * Remove default settings summary.
   */
  public function settingsSummary() {
    return [];
  }

  /**
   * {@inheritdoc}
   *
   * Re-implementation of viewElements from EntityReferenceLabelFormatter.
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $rows = [];
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');

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
      /** @var \Drupal\se_item\Entity\Item|\Drupal\se_timekeeping\Entity\Timekeeping $entity */
      $uri = $entity->toUrl();

      unset($item);
      switch ($entity->bundle()) {
        case 'se_timekeeping':
          $item = $items[$delta]->target_id;
          if ($timekeeping = Timekeeping::load($item)) {
            $cache_tags = Cache::mergeTags($cache_tags, $timekeeping->getCacheTags());
            $item = $timekeeping->se_it_ref->entity->se_code->value;
          }
          else {
            $cache_tags = Cache::mergeTags($cache_tags, $entity->getCacheTags());
          }
          break;

        case 'se_service':
        case 'se_stock':
        case 'se_recurring':
          $item = $entity->se_code->value;
          $cache_tags = Cache::mergeTags($cache_tags, $entity->getCacheTags());
          break;

        default:
          \Drupal::logger('ItemLineFormatter')
            ->error('Unhandled item type %type.', ['%type' => $entity->bundle()]);
          continue 2;
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

      $date = new DrupalDateTime($items[$delta]->completed_date, DateTimeItemInterface::STORAGE_TIMEZONE);
      $display_date = $date->getTimestamp() !== 0 ? gmdate('Y-m-d', $date->getTimestamp()) : '';

      $processed_text = $renderer->renderPlain($build);
      $processed = FilterProcessResult::createFromRenderArray($build)->setProcessedText((string) $processed_text);
      $processed_output = FilteredMarkup::create($processed->getProcessedText());

      $row = [
        $items[$delta]->quantity,
        $renderer->render($element),
        \Drupal::service('se_accounting.currency_format')->formatDisplay((int) $items[$delta]->price),
        $items[$delta]->serial,
        $display_date,
        $processed_output,
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

  /**
   * Overridden method that will render items for printing.
   *
   * @param \Drupal\Core\Field\EntityReferenceFieldItemListInterface $items
   *   Items to display.
   * @param string $langcode
   *   Language code.
   *
   * @return array|\Drupal\Core\Entity\EntityInterface[]
   *   Output for theme to work with.
   */
  protected function getEntitiesToView(EntityReferenceFieldItemListInterface $items, $langcode) {
    $entities = [];
    if (!$request = \Drupal::requestStack()->getCurrentRequest()) {
      return [];
    }

    foreach ($items as $delta => $item) {
      // Ignore items where no entity could be loaded in prepareView().
      if (!empty($item->_loaded)) {
        $entity = $item->entity;

        // Set the entity in the correct language for display.
        if ($entity instanceof TranslatableInterface) {
          $entity = \Drupal::service('entity.repository')->getTranslationFromContext($entity, $langcode);
        }

        $access = $this->checkAccess($entity);
        // Add the access result's cacheability, ::view() needs it.
        $item->_accessCacheability = CacheableMetadata::createFromObject($access);
        // Allow listing items for preview links.
        if ($access->isAllowed() || preg_match('/^\/preview-link\/.*?/', $request->getPathInfo())) {
          // Add the referring item, in case the formatter needs it.
          $entity->_referringItem = $items[$delta];
          $entities[$delta] = $entity;
        }
      }
    }

    return $entities;
  }

}
