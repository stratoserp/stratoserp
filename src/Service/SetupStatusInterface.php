<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Service;

interface SetupStatusInterface {

  /**
   * Return the current setup status from config.
   *
   * @return bool
   */
  public function isSetupComplete(): bool;

  /**
   * Display an error message if StratosERP setup is not complete.
   */
  public function setupStatusError(): void;

  /**
   * Perform the status checks and update config.
   *
   * @return bool
   */
  public function checkSetupStatus(): bool;

}
