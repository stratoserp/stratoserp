<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Simple controller to provide a basic frontpage until something better.
 */
class FrontPageController extends ControllerBase {

  /**
   * Provide some simple output for now.
   *
   * @return string[]
   *   The markup to display.
   *
   * @todo caching
   * @todo more stats, depending on role.
   */
  public function dashboard() {

    $layoutPluginManager = \Drupal::service('plugin.manager.core.layout');
    $blockPluginManager = \Drupal::service('plugin.manager.block');

    $layoutInstance = $layoutPluginManager->createInstance('layout_threecol_33_34_33', []);

    $regions = [
      'first' => $blockPluginManager->createInstance('user_timekeeping_statistics', [
        'title' => 'Timekeeping statistics',
      ])->build(),

      'second' => $blockPluginManager->createInstance('user_ticket_statistics', [
        'title' => 'Ticket statistics',
      ])->build(),

      'third' => $blockPluginManager->createInstance('user_invoice_statistics', [
        'title' => 'Invoice statistics',
      ])->build(),
    ];

    return $layoutInstance->build($regions);
  }

}
