<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Controller;

use Drupal\Core\Controller\ControllerBase;

class FrontPageController extends ControllerBase {

  public function statistics() {
    return [
      '#markup' => '<p>Business statistics to go here</p>',
    ];
  }

}
