<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Controller;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Layout\LayoutPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Simple controller to provide a basic frontpage until something better.
 */
class FrontPageController extends ControllerBase {

  /**
   * The layout plugin manager.
   *
   * @var \Drupal\Core\Layout\LayoutPluginManagerInterface
   */
  private LayoutPluginManagerInterface $layoutPluginManager;

  /**
   * The block mananger.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  private BlockManagerInterface $blockManager;

  /**
   * Simple constructor.
   *
   * @param \Drupal\Core\Layout\LayoutPluginManagerInterface $layoutPluginManager
   * @param \Drupal\Core\Block\BlockManagerInterface $blockManager
   */
  public function __construct(LayoutPluginManagerInterface $layoutPluginManager, BlockManagerInterface $blockManager) {
    $this->layoutPluginManager = $layoutPluginManager;
    $this->blockManager = $blockManager;
  }

  /**
   * Create function.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    $layoutPluginManager = $container->get('plugin.manager.core.layout');
    $blockPluginManager = $container->get('plugin.manager.block');

    return new static($layoutPluginManager, $blockPluginManager);
  }

  /**
   * Provide some simple output for now.
   *
   * @return string[]
   *   The markup to display.
   *
   * @todo caching
   * @todo more stats, depending on role.
   * @todo labels don't work.
   */
  public function dashboard() {

    $layoutInstance = $this->layoutPluginManager->createInstance('layout_threecol_33_34_33', []);

    $regions = [
      'first' => $this->blockManager->createInstance('user_timekeeping_statistics', [
        'label' => 'User timekeeping statistics',
      ])->build(),

      'second' => $this->blockManager->createInstance('user_ticket_statistics', [
        'label' => 'User ticket statistics',
      ])->build(),

      'third' => $this->blockManager->createInstance('user_invoice_statistics', [
        'label' => 'User invoice statistics',
      ])->build(),
    ];

    if (\Drupal::currentUser()->hasPermission('access company overview')) {
      $regions['first'][] = $this->blockManager->createInstance('company_timekeeping_statistics', [
        'label' => 'Company timekeeping statistics',
      ])->build();
      $regions['second'][] = $this->blockManager->createInstance('company_ticket_statistics', [
        'label' => 'Company ticket statistics',
      ])->build();
    }

    return $layoutInstance->build($regions);

  }

}
