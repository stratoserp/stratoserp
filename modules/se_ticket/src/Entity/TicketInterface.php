<?php

declare(strict_types=1);

namespace Drupal\se_ticket\Entity;

use Drupal\stratoserp\Entity\StratosEntityBaseInterface;

/**
 * Provides an interface for defining Ticket entities.
 *
 * @ingroup se_ticket
 */
interface TicketInterface extends StratosEntityBaseInterface {

  /**
   * Determine if a ticket is still open or closed.
   *
   * @return bool
   *   Whether the ticket has the scheduled fields entered.
   */
  public function isOpen(): bool;

  /**
   * Determine if a ticket has the scheduled fields set.
   *
   * @return bool
   *   Whether the ticket has the scheduled fields entered.
   */
  public function isScheduled(): bool;

  /**
   * Determine if this ticket matches the calenarable types.
   *
   * @return bool
   *   Whether the ticket has the scheduled fields entered.
   */
  public function isCalendarType(): bool;

  /**
   * Determine if a ticket should be shown in the calendar.
   *
   * @return bool
   *   Whether the ticket fields mean it should be in the calendar.
   */
  public function isCalendarWorthy(): bool;

}
