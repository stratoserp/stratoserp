<?php

namespace Drupal\se_invoice\Controller;

use Drupal\comment\Entity\Comment;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeTypeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\se_core\ErpCore;
use Drupal\se_item\Entity\Item;
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
   * Provides the node submission form for creation from a quote.
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
  public function add(NodeTypeInterface $node_type, Node $source) {
    $node = $this->entityTypeManager()->getStorage('node')->create([
      'type' => $node_type->id(),
    ]);

    // TODO - Make this a service?
    foreach ($source->{'field_' . ErpCore::ITEMS_BUNDLE_MAP[$source->bundle()] . '_items'} as $index => $value) {
      $new_value = $value->getValue();
      if ($source_paragraph = Paragraph::load($new_value['target_id'])) {
        $node->{'field_' . ErpCore::ITEMS_BUNDLE_MAP[$node->bundle()] . '_items'}->appendItem($source_paragraph->createDuplicate());
      }
    }

    $node->{'field_' . ErpCore::ITEMS_BUNDLE_MAP[$node->bundle()] . '_quote_ref'}->target_id = $source->id();

    return $this->entityFormBuilder()->getForm($node);
  }

  /**
   * Provides the node submission form for creation from timekeeping entries.
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
  public function timekeeping(NodeTypeInterface $node_type, Node $source) {
    $node = $this->entityTypeManager()->getStorage('node')->create([
      'type' => $node_type->id(),
    ]);

    $default_status = \Drupal::config('se_invoice.settings')->get('invoice_status_term');
    $open = Term::load($default_status);

    $query = \Drupal::request()->query;
    if (!$customer_id = $query->get('field_bu_ref')) {
      return $this->entityFormBuilder()->getForm($node);
    }

    $total = 0;

    // Retrieve a list of unbilled timekeeping entries for this customer.
    $query = \Drupal::entityQuery('comment');
    $query->condition('comment_type', 'se_timekeeping');
    $query->condition('field_bu_ref', $customer_id);
    $query->condition('field_tk_billed', FALSE);
    $query->condition('field_tk_billable', TRUE);
    $entity_ids = $query->execute();

    foreach ($entity_ids as $entity_id) {
      if ($comment = $this->entityTypeManager()->getStorage('comment')->load($entity_id)) {
        $paragraph = Paragraph::create(['type' => 'se_items']);
        $paragraph->set('field_it_line_item', [
          'target_id' => $comment->id(),
          'target_type' => 'comment',
        ]);
        $paragraph->set('field_it_quantity', $comment->field_tk_amount->seconds / 3600);
        $paragraph->set('field_it_description', [
          'value' => $comment->field_tk_comment->value,
          'format' => $comment->field_tk_comment->format,
        ]);
        if ($item = Item::load($comment->field_tk_item->target_id)) {
          $paragraph->set('field_it_price', $item->field_it_sell_price->value);
        }
        $node->{'field_' . ErpCore::ITEMS_BUNDLE_MAP[$node->bundle()] . '_items'}->appendItem($paragraph);
      }
    }

    if ($open) {
      $node->field_pa_status_ref->target_id = $open->id();
    }

    return $this->entityFormBuilder()->getForm($node);
  }



}
