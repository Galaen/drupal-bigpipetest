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

    $now = time();
    $count  = 5;

    $res = $this->opendatasoftApi->getWeatherForecastParis();
    //dump($res);
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
    //dump($rows);

    $build['table'] = [
      '#type' => 'table',
//      '#caption' => $this->t('Weather Forecast'),
//      '#header' => [$this->t('Date'), $this->t('Temperature')],
      '#caption' => t('Weather Forecast'),
      '#header' => [t('Date'), t('Temperature')],
      '#rows' => $rows,
    ];

    return $build;

  }
}
