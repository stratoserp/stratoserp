<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeTypeInterface;
use Drupal\stratoserp\ErpCore;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Node routes.
 */
class NodeController extends ControllerBase {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected DateFormatterInterface $dateFormatter;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected RendererInterface $renderer;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected EntityRepositoryInterface $entityRepository;

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
  public function __construct(DateFormatterInterface $date_formatter, RendererInterface $renderer, EntityRepositoryInterface $entity_repository) {
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
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
   * Provides the node submission form for creation from a quote.
   *
   * @param \Drupal\node\NodeTypeInterface $nodeType
   *   The node type entity for the node.
   * @param \Drupal\node\Entity\Node $source
   *   Source node to copy data from.
   *
   * @return array
   *   A node submission form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function add(NodeTypeInterface $nodeType, Node $source) {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->entityTypeManager()->getStorage('node')->create([
      'type' => $nodeType->id(),
    ]);

    $total = 0;
    $sourceFieldType = 'se_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$source->bundle()];
    $bundleFieldType = 'se_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$node->bundle()];

    // @todo Make this a service.
    /**
     * @var int $index
     * @var \Drupal\se_item_line\Plugin\Field\FieldType\ItemLineType $item
     */
    foreach ($source->{$sourceFieldType . '_lines'} as $item) {
      $node->{$bundleFieldType . '_lines'}->appendItem($item->getValue());
    }

    $node->se_bu_ref->target_id = $source->se_bu_ref->target_id;
    $node->se_co_ref->target_id = $source->se_co_ref->target_id;
    $node->{$bundleFieldType . '_quote_ref'}->target_id = $source->id();
    $node->{$bundleFieldType . '_total'} = $total;

    return $this->entityFormBuilder()->getForm($node);
  }

  /**
   * Provides the node submission form for creation from timekeeping entries.
   *
   * @param \Drupal\node\NodeTypeInterface $nodeType
   *   The node type entity for the node.
   * @param \Drupal\node\Entity\Node $source
   *   The source node.
   *
   * @return array
   *   A node submission form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function timekeeping(NodeTypeInterface $nodeType, Node $source): array {
    $node = $this->createNodeFromTimekeeping($nodeType, $source);

    return $this->entityFormBuilder()->getForm($node);
  }

  /**
   * Provides the node submission form for creation from timekeeping entries.
   *
   * @param \Drupal\node\NodeTypeInterface $nodeType
   *   The node type entity for the node.
   * @param \Drupal\node\Entity\Node $source
   *   The source node.
   *
   * @return array
   *   A node submission form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function createNodeFromTimekeeping(NodeTypeInterface $nodeType, Node $source) {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->entityTypeManager()->getStorage('node')->create([
      'type' => $nodeType->id(),
    ]);

    $defaultStatus = \Drupal::config('se_invoice.settings')->get('invoice_status_term');
    $open = Term::load($defaultStatus);

    // Retrieve a list of non billed timekeeping entries for this customer.
    $query = \Drupal::entityQuery('comment');

    if ($source->bundle() !== 'se_customer') {
      if (!$source->se_bu_ref->target_id) {
        return $this->entityFormBuilder()->getForm($node);
      }
    }

    $query->condition('comment_type', 'se_timekeeping')
      ->condition('se_bu_ref', $source->id())
      ->condition('se_tk_billed', TRUE, '<>')
      ->condition('se_tk_billable', TRUE)
      ->condition('se_tk_amount', 0, '>');
    $entityIds = $query->execute();

    $total = 0;
    $lines = [];
    $bundleFieldType = 'se_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$node->bundle()];

    // Loop through the timekeeping entries and setup invoice lines.
    foreach ($entityIds as $entityId) {
      /** @var \Drupal\comment\Entity\Comment $comment */
      if ($comment = $this->entityTypeManager()->getStorage('comment')->load($entityId)) {
        /** @var \Drupal\se_item\Entity\Item $item */
        if ($item = $comment->se_tk_item->entity) {
          $price = (int) $item->se_it_sell_price->value;
          $line = [
            'target_type' => 'comment',
            'target_id' => $comment->id(),
            'quantity' => round($comment->se_tk_amount->value / 60, 2),
            'notes' => $comment->se_tk_comment->value,
            'format' => $comment->se_tk_comment->format,
            'price' => $price,
          ];
          $lines[] = $line;
          $total += $line['quantity'] * $line['price'];
        }
        else {
          \Drupal::logger('se_timekeeping')->error('No matching item for entry @cid', ['@cid' => $comment->id()]);
        }
      }
    }

    $node->{$bundleFieldType . '_lines'} = $lines;
    $node->{$bundleFieldType . '_total'} = $total;

    if ($open) {
      $node->se_status_ref->target_id = $open->id();
    }

    return $node;
  }

}
