<?php

namespace Drupal\se_core\Event;

use Symfony\Component\EventDispatcher\Event;

class SeCoreEvent extends Event {

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
