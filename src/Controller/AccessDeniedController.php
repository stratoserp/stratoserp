<?php

namespace Drupal\stratoserp\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class AccessDeniedController extends ControllerBase {

  /**
   * Returns a simple access denied page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function provideAccessDeniedPage() {
    return [
      '#markup' => 'Login is required.',
    ];
  }

}
