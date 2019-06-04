<?php

namespace Drupal\se_timekeeping\Service;

class TimeFormat {

  public function formatHours($value) {
    if (!empty($value) && $value = (int)$value) {
      return sprintf('%-1.2f', $value / 60);
    }
    return 0;
  }

}