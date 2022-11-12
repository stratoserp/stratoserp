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
    /** @var \Drupal\se_accounting\Service\CurrencyFormatServiceInterface $currencyFormatter */
    $currencyFormatter = \Drupal::service('se_accounting.currency_format');
    $rows = [];

    $headers = [
      ['class' => 'item', 'data' => t('Item')],
      ['class' => 'quantity', 'data' => t('Qty')],
      ['class' => 'date', 'data' => t('Date')],
      ['class' => 'notes', 'data' => t('Notes')],
      ['class' => 'serial', 'data' => t('Serial')],
      ['class' => 'price', 'data' => t('Price')],
    ];

    $cacheTags = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      /** @var \Drupal\se_item\Entity\Item|\Drupal\se_timekeeping\Entity\Timekeeping $entity */
      $uri = $entity->toUrl();

      $itemCode = '';
      $itemDescription = '';
      switch ($entity->bundle()) {
        case 'se_timekeeping':
          $timekeepingEntity = $items[$delta]->target_id;
          if ($timekeeping = Timekeeping::load($timekeepingEntity)) {
            $cacheTags = Cache::mergeTags($cacheTags, $timekeeping->getCacheTags());
            $itemCode = $timekeeping->se_it_ref->entity->se_code->value;
            $itemDescription = $timekeeping->se_it_ref->entity->se_description->value;
          }
          else {
            $cacheTags = Cache::mergeTags($cacheTags, $entity->getCacheTags());
          }
          break;

        case 'se_service':
        case 'se_stock':
        case 'se_recurring':
          $itemCode = $entity->se_code->value;
          $itemDescription = $entity->se_description->value;
          $cacheTags = Cache::mergeTags($cacheTags, $entity->getCacheTags());
          break;

        default:
          \Drupal::logger('ItemLineFormatter')
            ->error('Unhandled item type %type.', ['%type' => $entity->bundle()]);
          continue 2;
      }

      $itemBuild = [
        '#type' => 'link',
        '#title' => $itemCode,
        '#url' => $uri,
        '#options' => $uri->getOptions(),
      ];
      if (!empty($items[$delta]->_attributes)) {
        $itemBuild['#options'] += ['attributes' => []];
        $itemBuild['#options']['attributes'] += $items[$delta]->_attributes;
        // Unset field item attributes since they have been included in the
        // formatter output and shouldn't be rendered in the field template.
        unset($items[$delta]->_attributes);
      }

      // Setup date field.
      $date = new DrupalDateTime($items[$delta]->completed_date, DateTimeItemInterface::STORAGE_TIMEZONE);
      $dateBuild = $date->getTimestamp() !== 0 ? gmdate('Y-m-d', $date->getTimestamp()) : '';

      // Transform the notes from the stored value into something
      // safe to display.
      $notesBuild = [
        '#type' => 'processed_text',
        '#text' => '<p>' . nl2br($itemDescription ?: '') . '</p><p>' . nl2br($items[$delta]->note ?: '') . '</p>',
        '#format' => $items[$delta]->format,
        '#filter_types_to_skip' => [],
        '#langcode' => $items[$delta]->getLangcode(),
      ];

      $row = [
        ['class' => 'item', 'data' => $itemBuild],
        ['class' => 'quantity', 'data' => $items[$delta]->quantity],
        ['class' => 'date', 'data' => $dateBuild],
        ['class' => 'notes', 'data' => $notesBuild],
        ['class' => 'serial', 'data' => $items[$delta]->serial],
        [
          'class' => 'price',
          'data' => $currencyFormatter->formatDisplay((int) $items[$delta]->price),
        ],
      ];
      $rows[] = $row;
    }

    // Now wrap the lines into a bundle with cache tags.
    return [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $headers,
      '#cache' => [
        'tags' => $cacheTags,
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
