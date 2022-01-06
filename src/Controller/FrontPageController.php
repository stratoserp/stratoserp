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
   */
  public function statistics() {
    return [
      '#markup' => '<p>Business statistics to go here</p>',
    ];
  }

}
