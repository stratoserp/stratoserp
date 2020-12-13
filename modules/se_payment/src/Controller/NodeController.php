<?php

declare(strict_types=1);

namespace Drupal\se_payment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\NodeTypeInterface;
use Drupal\se_payment\Traits\ErpPaymentTrait;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Node routes.
 */
class NodeController extends ControllerBase {

  use ErpPaymentTrait;

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
   * @return array
   *   A node submission form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function add(NodeTypeInterface $node_type): array {
    /** @var \Drupal\node\Entity\Node $node */
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
    if (!$customer_id = $query->get('se_bu_ref')) {
      return $this->entityFormBuilder()->getForm($node);
    }

    $total = 0;
    $query = \Drupal::entityQuery('node');
    $group = $query->orConditionGroup()
      ->condition('se_status_ref', $open->id())
      ->notExists('se_status_ref');

    $query->condition('type', 'se_invoice')
      ->condition('se_bu_ref', $customer_id)
      ->condition($group);

    $entity_ids = $query->execute();

    // Build a list of outstanding invoices and make payment lines out of them.
    $lines = [];
    foreach ($entity_ids as $id) {
      /** @var \Drupal\node\Entity\Node $invoice */
      if ($invoice = $this->entityTypeManager()->getStorage('node')->load($id)) {
        $outstanding_amount = $this->getInvoiceBalance($invoice);
        $line = [
          'target_id' => $invoice->id(),
          'target_type' => 'node',
          'amount'  => $outstanding_amount,
          'payment_type' => $payment_term->id(),
        ];
        $lines[] = $line;
      }
    }

    $node->se_pa_lines = $lines;
    $node->se_pa_total = $total;

    return $this->entityFormBuilder()->getForm($node);
  }

}
