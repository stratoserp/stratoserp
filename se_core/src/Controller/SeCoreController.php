<?php

namespace Drupal\se_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Controller routines for page example routes.
 */
class SeCoreController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  protected function getModuleName() {
    return 'se_core';
  }

  /**
   * Provide the Stratos ERP home page.
   *
   * @return array
   *   The render array.
   */
  public function home() {
    $list[] = $this->t('Put things here');
    $list[] = $this->t('So that things look good');

    $render_array['se_core_home'] = [
      '#theme' => 'item_list',
      '#items' => $list,
    ];
    return $render_array;
  }

}
