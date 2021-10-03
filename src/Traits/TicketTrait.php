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
    $closedList = \Drupal::configFactory()->getEditable('se_ticket.settings')->get('se_ticket_calendar_status_list');

    if (is_array($closedList) && !in_array($ticket->se_ti_status_ref->value, $closedList, TRUE)) {
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

    if (!isset($ticket->se_ti_scheduled->value)) {
      return FALSE;
    }

    $calendarList = \Drupal::configFactory()->getEditable('se_ticket.settings')->get('se_ticket_calendar_type_list');

    if (!in_array($ticket->se_ti_type_ref->value, $calendarList, TRUE)) {
      return TRUE;
    }
  }

}

