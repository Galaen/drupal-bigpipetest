<?php

namespace Drupal\bigpipetest;

use Drupal\Core\Session\SessionManagerInterface;
use Drupal\opendatasoft\APIServiceInterface;

/**
 * Class ForecastGeneratorService.
 */
class ForecastGeneratorService implements ForecastGeneratorServiceInterface {

  /**
   * Drupal\opendatasoft\APIServiceInterface definition.
   *
   * @var \Drupal\opendatasoft\APIServiceInterface
   */
  protected $opendatasoftApi;

  /**
   * The session manager.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  protected $sessionManager;

  /**
   * Constructs a new ForecastGeneratorService object.
   *
   * @param \Drupal\opendatasoft\APIServiceInterface $opendatasoft_api
   *   Service to abstract OpenDataSoft API.
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   The session manager.
   */
  public function __construct(APIServiceInterface $opendatasoft_api, SessionManagerInterface $session_manager) {
    $this->opendatasoftApi = $opendatasoft_api;
    $this->sessionManager = $session_manager;
  }

  public function generateWeatherForecast(bool $session_started, int $cache_duration) {
    //$sessionStarted = $this->sessionManager->isStarted();

    $rows = [];

    $cid = 'bigpipetest:weatherforecast';
    $build = NULL;
    if (($cache = \Drupal::cache()->get($cid)) && $session_started) {
      $build = $cache->data;
    }
    else {
      $now = time();
      $count  = 5;

      $res = $this->opendatasoftApi->getWeatherForecastParis();
      if (!empty($res['records'])) {
        foreach ($res['records'] as $record) {
          if (!empty($record['fields']['forecast']) && !empty($record['fields']['2_metre_temperature'])) {
            $date = new \DateTime($record['fields']['forecast']);
            if ($date->getTimestamp() > $now) {
              $rows[] = [$date->format('H:i'), round($record['fields']['2_metre_temperature'], 1)];
              $count--;
              if ($count <= 0)
                break;
            }
          }
        }
      }

      $build['is_cached']['#markup'] = t('<p>Serving CACHED version<br><small>Cache duration: @time sec.</small></p>', ['@time' => $cache_duration]);
      $build['time']['#markup'] = t('<p><small>Generation timestamp: @timestamp</small></p>', ['@timestamp' => $now]);

      $build['table'] = [
        '#type' => 'table',
        //      '#caption' => $this->t('Weather Forecast'),
        //      '#header' => [$this->t('Date'), $this->t('Temperature')],
        '#caption' => t('Weather Forecast'),
        '#header' => [t('Date'), t('Temperature')],
        '#rows' => $rows,
      ];

      if ($session_started) {
        \Drupal::cache()->set($cid, $build, $now + $cache_duration, ['weather_forecast_block']);
        $build['is_cached']['#markup'] = t('<p>Serving WEB SERVICE version</p>');
      }
      else
        $build['is_cached']['#markup'] = t('<p>Serving NO SESSION CACHED version<br><small>Cache duration: @time sec.</small><br><small>This only works if internal page cache is not activated.</small><br><small>Invalidate cache tag to rebuild pages.</small></p>', ['@time' => $cache_duration]);
    }


    return $build;

  }
}
