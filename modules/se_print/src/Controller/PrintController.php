<?php

declare(strict_types=1);

namespace Drupal\se_print\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\entity_print\Plugin\EntityPrintPluginManagerInterface;
use Drupal\entity_print\Plugin\ExportTypeManagerInterface;
use Drupal\entity_print\PrintBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create our own print controller that will callable via a local action.
 */
class PrintController extends ControllerBase {

  /**
   * The plugin manager for our Print engines.
   *
   * @var \Drupal\entity_print\Plugin\EntityPrintPluginManagerInterface
   */
  protected $pluginManager;

  /**
   * The export type manager.
   *
   * @var \Drupal\entity_print\Plugin\ExportTypeManagerInterface
   */
  protected $exportTypeManager;

  /**
   * The Print builder.
   *
   * @var \Drupal\entity_print\PrintBuilderInterface
   */
  protected $printBuilder;

  /**
   * The Print engine.
   *
   * @var \Drupal\entity_print\PrintBuilderInterface
   */
  protected $printEngine;

  /**
   * The Entity Type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityPrintPluginManagerInterface $plugin_manager, ExportTypeManagerInterface $export_type_manager, PrintBuilderInterface $print_builder, EntityTypeManagerInterface $entity_type_manager) {
    $this->pluginManager = $plugin_manager;
    $this->exportTypeManager = $export_type_manager;
    $this->printBuilder = $print_builder;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.entity_print.print_engine'),
      $container->get('plugin.manager.entity_print.export_type'),
      $container->get('entity_print.print_builder'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Providing printing functionality for local action.
   *
   * @param \Drupal\Core\Entity\EntityInterface $source
   *   The source entity to print.
   *
   * @return array
   *   Markup.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function entity(EntityInterface $source): array {
    $print_engine = $this->pluginManager->createSelectedInstance('pdf');

    $filename = $source->generateFilename() . '.pdf';

    // Use private files, not sharing publicly.
    $uri = $this->printBuilder->savePrintable([$source], $print_engine, 'private', $filename, FALSE);

    return [
      '#markup' => $uri,
    ];
  }

}
