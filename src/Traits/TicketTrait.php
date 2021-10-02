<?php

namespace Drupal\stratoserp\Traits;

use Drupal\se_ticket\Entity\Ticket;

/**
 * Trait for Ticket status events.
 *
 * @package Drupal\stratoserp\Traits
 */
trait TicketTrait {

  /**
   * Determine if a ticket is still Open or closed.
   *
   * @todo Provide a selection form for closed status values.
   */
  public function isOpen(Ticket $ticket) {
    /** @var \Drupal\taxonomy\Entity\Term $closed */

    $closedList[] = stratoserp_get_term_id_by_name('Closed', 'se_ticket_status');
    $closedList[] = stratoserp_get_term_id_by_name('Cancelled', 'se_ticket_status');

    if (!in_array($ticket->se_ti_status_ref->value, $closedList, TRUE)) {
      return TRUE;
    }
  }

  /**
   * Determine if a ticket should be shown in the calendar.
   *
   * @todo Provide a selection form for conditions.
   */
  public function isCalenderWorthy(Ticket $ticket) {
    if (!$this->isOpen($ticket)) {
      return FALSE;
    }

    $nonCalendarList[] = stratoserp_get_term_id_by_name('In store', 'se_ticket_type');
    $nonCalendarList[] = stratoserp_get_term_id_by_name('Warranty', 'se_ticket_type');

    if (!in_array($ticket->se_ti_type_ref->value, $nonCalendarList, TRUE)) {
      return TRUE;
    }
  }

}

