<?php

namespace Drupal\opendatasoft;

/**
 * Interface APIServiceInterface.
 */
interface APIServiceInterface {


  /**
   * Get Weather Forecast Paris
   *
   * @return bool|mixed
   */
  public function getWeatherForecastParis();

}
