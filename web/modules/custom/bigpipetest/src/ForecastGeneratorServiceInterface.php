<?php

namespace Drupal\bigpipetest;

/**
 * Interface ForecastGeneratorServiceInterface.
 */
interface ForecastGeneratorServiceInterface {

  /**
   * Generate a Weather forecast block content
   *
   * @param bool $session_started
   *   TRUE if the current user has a session
   * @param int $cache_duration
   *   Duration of the cache in sec
   * @return
   */
  public function generateWeatherForecast(bool $session_started, int $cache_duration);

}
