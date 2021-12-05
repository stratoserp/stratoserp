<?php

declare(strict_types=1);

namespace Drupal\se_devel\EventSubscriber;

use Drupal\config_devel\Event\ConfigDevelEvents;
use Drupal\config_devel\Event\ConfigDevelSaveEvent;
use Drupal\Core\Config\ConfigCrudEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for config_devel events to exclude specific content values.
 */
class ConfigDevelEventSubscriber implements EventSubscriberInterface {

  /**
   * React to configuration ConfigEvent::SAVE events.
   *
   * @param \Drupal\config_devel\Event\ConfigDevelSaveEvent $event
   *   The event to process.
   */
  public function onConfigSave(ConfigDevelSaveEvent $event): void {
    $data = $event->getData();

    // Ensure we only deal with our own config.
    $fileNames = $event->getFileNames();
    foreach ($fileNames as $fileName) {
      if (stripos($fileName, 'stratoserp') === FALSE) {
        return;
      }
    }
    $fileName = $fileNames[0];

    // Remove default values for entity reference fields.
    if (isset($data['field_type']) && $data['field_type'] === 'entity_reference') {
      $data['default_value'] = [];
    }

    // Remove blank settings in the item line fields.
    if (isset($data['field_type']) && $data['field_type'] === 'se_item_line') {
      $newSettings = [];
      foreach ($data['settings'] as $entity => $entitySettings) {
        if (is_array($entitySettings)
        && is_array($entitySettings['handler_settings'])
        && !empty($entitySettings['handler_settings'])) {
          $newSettings[$entity] = $entitySettings;
        }
      }
      $data['settings'] = $newSettings;
    }

    // Remove taxonomy term content dependencies.
    if (isset($data['dependencies']['content'])) {
      $newContent = [];
      foreach ($data['dependencies']['content'] as $value) {
        if (stripos($value, 'taxonomy_term') === FALSE) {
          $newContent[] = $value;
        }
      }
      $data['dependencies']['content'] = $newContent;
    }

    // Remove very specific ticket fields.
    if (isset($data['id'])) {
      if ($data['id'] === 'se_ticket.se_ticket.default') {
        if ((stripos($fileName, 'core.entity_form_display') !== FALSE)
        || (stripos($fileName, 'core.entity_view_display') !== FALSE)) {
          unset($data['content']['se_timekeeping']);
          if (($key = array_search('field.field.se_ticket.se_ticket.se_timekeeping', $data['dependencies']['config'], TRUE)) !== false) {
            unset($data['dependencies']['config'][$key]);
          }
        }
      }
    }

    $event->setData($data);
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  static function getSubscribedEvents() {
    $events[ConfigDevelEvents::SAVE][] = array('onConfigSave', 50);
    return $events;
  }

}
