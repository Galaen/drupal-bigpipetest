<?php

namespace Drupal\bigpipetest;

/**
 * Interface ForecastGeneratorServiceInterface.
 */
interface ForecastGeneratorServiceInterface {

  /**
   * Generate a Weather forecast block content
   */
  public function generateWeatherForecast();

}
