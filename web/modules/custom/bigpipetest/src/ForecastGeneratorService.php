<?php

namespace Drupal\bigpipetest;
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
   * Constructs a new ForecastGeneratorService object.
   */
  public function __construct(APIServiceInterface $opendatasoft_api) {
    $this->opendatasoftApi = $opendatasoft_api;
  }

  public function generateWeatherForecast() {
    $rows = [];

    $cid = 'bigpipetest:weatherforecast';
    $build = NULL;
    if ($cache = \Drupal::cache()->get($cid)) {
      $build = $cache->data;
    }
    else {
      $now = time();
      $cacheDuration = 30;  // 30 sec.

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

      $build['is_cached']['#markup'] = t('<p>Serving CACHED version (@time sec.)</p>', ['@time' => $cacheDuration]);
      $build['time']['#markup'] = t('<p><small>Generation timestamp: @timestamp</small></p>', ['@timestamp' => $now]);

      $build['table'] = [
        '#type' => 'table',
        //      '#caption' => $this->t('Weather Forecast'),
        //      '#header' => [$this->t('Date'), $this->t('Temperature')],
        '#caption' => t('Weather Forecast'),
        '#header' => [t('Date'), t('Temperature')],
        '#rows' => $rows,
      ];

      //$data = my_module_complicated_calculation();
      \Drupal::cache()->set($cid, $build, $now + $cacheDuration);

      $build['is_cached']['#markup'] = 'Serving WEB SERVICE version';
    }



    return $build;

  }
}
