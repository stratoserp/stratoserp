<?php

namespace Drupal\se_payment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\NodeTypeInterface;
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
   * Provides the node submission form.
   *
   * @param \Drupal\node\NodeTypeInterface $node_type
   *   The node type entity for the node.
   *
   * @param \Drupal\node\Entity\Node $source
   *
   * @return array
   *   A node submission form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function add(NodeTypeInterface $node_type) {
    $node = $this->entityTypeManager()->getStorage('node')->create([
      'type' => $node_type->id(),
    ]);

    if ((!$invoice_status = \Drupal::config('se_invoice.settings')->get('invoice_status_term'))
      || !$open = Term::load($invoice_status)) {
      \Drupal::messenger()->addWarning('No invoice status term issue, unable to retrieve list of open invoices automatically.');
      return $this->entityFormBuilder()->getForm($node);
    }

    $payment_term = NULL;
    if ($payment_type = \Drupal::config('se_payment.settings')->get('default_payment_term')) {
      $payment_term = Term::load($payment_type);
    }

    $query = \Drupal::request()->query;
    if (!$customer_id = $query->get('field_bu_ref')) {
      return $this->entityFormBuilder()->getForm($node);
    }

    $total = 0;
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'se_invoice');
    $query->condition('field_bu_ref', $customer_id);
    $query->condition('field_status_ref', $open->id());
    $entity_ids = $query->execute();

    // Build a list of outstanding invoices and make payment lines out of them.
    $lines = [];
    foreach ($entity_ids as $id) {
      /** @var \Drupal\node\Entity\Node $invoice */
      if ($invoice = $this->entityTypeManager()->getStorage('node')->load($id)) {
        $line = [
          'target_id' => $invoice->id(),
          'target_type' => 'node',
          'amount'  => $invoice->field_in_total->value,
          'payment_type' => $payment_term->id(),
        ];
        $lines[] = $line;
      }
    }

    $node->{'field_pa_lines'} = $lines;

    return $this->entityFormBuilder()->getForm($node);
  }

}
