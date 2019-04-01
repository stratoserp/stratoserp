<?php

namespace Drupal\se_payment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeTypeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\se_core\ErpCore;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Node routes.
 */
class NodeController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Constructs a NodeController object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(DateFormatterInterface $date_formatter, RendererInterface $renderer, EntityRepositoryInterface $entity_repository = NULL) {
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
    if (!$entity_repository) {
      @trigger_error('The entity.repository service must be passed to NodeController::__construct(), it is required before Drupal 9.0.0. See https://www.drupal.org/node/2549139.', E_USER_DEPRECATED);
      $entity_repository = \Drupal::service('entity.repository');
    }
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer'),
      $container->get('entity.repository')
    );
  }

  /**
   * Provides the node submission form.
   *
   * @param \Drupal\node\NodeTypeInterface $node_type
   *   The node type entity for the node.
   *
   * @param \Drupal\node\Entity\Node $source
   *
   * @return array
   *   A node submission form.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function add(NodeTypeInterface $node_type) {
    $node = $this->entityTypeManager()->getStorage('node')->create([
      'type' => $node_type->id(),
    ]);

    // TODO - Change to be a setting and then use that.
    $term = taxonomy_term_load_multiple_by_name('Open', 'se_status');
    $open = reset($term);

    $query = \Drupal::request()->query;
    if (!$customer_id = $query->get('field_bu_ref')) {
      return $this->entityFormBuilder()->getForm($node);
    }

    $total = 0;
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'se_invoice');
    $query->condition('field_bu_ref', $customer_id);
    $query->condition('field_in_status_ref', $open->id());
    $entity_ids = $query->execute();

    // Build a list of outstanding invoices and make paragraphs out of them.
    foreach ($entity_ids as $id) {
      if ($invoice = Node::load($id)) {
        $paragraph = Paragraph::create(['type' => 'se_payments']);
        $paragraph->set('field_pa_invoice', [
          'target_id' => $id,
          'target_type' => 'se_invoice'
        ]);
        $paragraph->set('field_pa_amount', $invoice->field_in_total->value);
        $node->{'field_pa_items'}->appendItem($paragraph);

        $total += $invoice->field_in_total->value;
      }
    }

    $node->field_pa_status_ref->target_id = $open->id();
    $node->{'field_pa_items'}->value = $total;

    return $this->entityFormBuilder()->getForm($node);
  }

}
