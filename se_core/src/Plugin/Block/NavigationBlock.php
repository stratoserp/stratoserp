<?php

namespace Drupal\se_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\node\NodeInterface;

/**
 * Provides a 'NavigationBlock' block.
 *
 * @Block(
 *  id = "navigation_block",
 *  admin_label = @Translation("Stratos ERP Navigation"),
 * )
 */
class NavigationBlock extends BlockBase {

  /** @var NodeInterface */
  protected $node;

  protected $button_class;

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $this->button_class = ['attributes' => ['class' => ['btn', 'btn-xs', 'btn-success']]];
    $this->node = \Drupal::routeMatch()->getParameter('node');

    if ($this->node instanceof NodeInterface) {

      switch($this->node->getType()) {
        case 'se_customer':
          $items = $this->customerLinks();
          break;

        case 'se_contact':
          $items = $this->contactLinks();
          break;

        case 'se_quote':
          $items = $this->quoteLinks();
          break;

        case 'se_invoice':
          $items = $this->invoiceLinks();
          break;

        case 'se_goods_receipt':
          $items = $this->goodsReceiptLinks();
          break;

        case 'se_purchase_order':
          $items = $this->purchaseOrderLinks();
          break;
      }

      if (isset($items)) {
        $build['navigation_block'] = [
          '#theme' => 'item_list',
          '#attributes' => ['class' => 'list-inline local-actions'],
          '#items' => $items,
        ];
      }
    }

    return $build;
  }

  public function getCacheTags() {
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      return Cache::mergeTags(parent::getCacheTags(), array('node:' . $node->id()));
    } else {
      return parent::getCacheTags();
    }
  }

  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }

  private function customerLinks() {
    $items = [];

    $items[] = Link::createFromRoute('Add contact', 'node.add', [
      'node_type' => 'se_contact',
      'business_ref' => $this->node->id()
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add customer', 'node.add', [
      'node_type' => 'se_customer',
      'business_ref' => $this->node->id()
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add document', 'node.add', [
      'node_type' => 'se_document',
      'business_ref' => $this->node->id()
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add ticket', 'node.add', [
      'node_type' => 'se_ticket',
      'business_ref' => $this->node->id()
    ], $this->button_class);
    return $items;
  }

  private function contactLinks() {
    $items = [];

    $items[] = Link::createFromRoute('Add customer', 'node.add', [
      'node_type' => 'se_customer',
      'contact_ref' => $this->node->id()
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add quote', 'node.add', [
      'node_type' => 'se_quote',
      'contact_ref' => $this->node->id()
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add document', 'node.add', [
      'node_type' => 'se_document',
      'contact_ref' => $this->node->id()
    ], $this->button_class);
    $items[] = Link::createFromRoute('Add ticket', 'node.add', [
      'node_type' => 'se_ticket',
      'contact_ref' => $this->node->id()
    ], $this->button_class);

    return $items;
  }

  private function quoteLinks() {
    $items = [];

    $items[] = Link::createFromRoute('Add purchase order', 'node.add', [
      'node_type' => 'se_purchase_order',
      'business_ref' => $this->node->id()
    ], $this->button_class);

    return $items;
  }

  private function invoiceLinks() {
    $items = [];

    $items[] = Link::createFromRoute('Add payment', 'node.add', [
      'node_type' => 'se_purchase_order',
      'business_ref' => $this->node->id()
    ], $this->button_class);

    return $items;
  }

  private function goodsReceiptLinks() {
    $items = [];

//    $items[] = Link::createFromRoute('Add purchase order', 'node.add', [
//      'node_type' => 'se_purchase_order',
//      'business_ref' => $this->node->id()
//    ], $this->button_class);

    return $items;
  }

  private function purchaseOrderLinks() {
    $items = [];

    $items[] = Link::createFromRoute('Add goods receipt', 'node.add', [
      'node_type' => 'se_purchase_order',
      'business_ref' => $this->node->id()
    ], $this->button_class);

    return $items;
  }



}
