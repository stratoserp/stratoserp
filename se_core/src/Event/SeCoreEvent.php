<?php

namespace Drupal\se_core\Event;

use Drupal\node\Entity\Node;
use Symfony\Component\EventDispatcher\Event;

class SeCoreEvent extends Event {

  /**
   * @var Node $node;
   */
  protected $node;

  /**
   * Constructs a event object.
   *
   * @param object $node
   *   The node being saved/deleted.
   */
  public function __construct($node) {
    $this->node = $node;
  }

  /**
   * Get the class node value.
   *
   * @return Node;
   */
  public function getNode() {
    return $this->node;
  }

  /**
   * Set the class node value.
   *
   * @param object $node
   *   The node being worked on.
   */
  public function setNode($node) {
    $this->node = $node;
  }

}
