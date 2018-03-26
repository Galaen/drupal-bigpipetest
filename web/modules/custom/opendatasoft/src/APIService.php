<?php

namespace Drupal\opendatasoft;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class APIService.
 */
class APIService implements APIServiceInterface {

  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;
  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;
  /**
   * The service settings config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $serviceConfig;

  /**
   * Constructs a new APIService object.
   * @param ClientInterface $http_client
   *   The HTTP Client.
   * @param ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ClientInterface $http_client, ConfigFactoryInterface $config_factory) {
    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;
    $this->serviceConfig = $config_factory->get('opendatasoft.servicesettings');
  }

  public function getWeatherForecastParis() {
    $service = 'records/1.0/search/';
    $query = $this->createQuery();
    $query['sort'] = '-forecast';
    $query['geofilter.distance'] = '48.9,2.35';
    $query['rows'] = 100;

    return $this->request($service, $query);
  }

  /**
   * Create the basic query parameters
   *
   * @return array
   */
  protected function createQuery() {
    $query = [
      'format' => 'json',
      'dataset' => 'arome-0025-sp1_sp2_paris',
      'timezone' => 'Europe/Berlin',
    ];

    return $query;
  }

  /**
   * Do the request and return the json
   *
   * @param $service
   * @param $query
   *
   * @return bool|mixed
   */
  protected function request($service, $query) {

    try {
      $request_url = $this->serviceConfig->get('ws_url') . $service;
      $response = $this->httpClient->request('GET', $request_url, ['query' => $query]);
//      $this->httpClient->request('GET', $request_url, ['query' => $query,'on_stats' => function (TransferStats $stats) use (&$url) {
//        $url = $stats->getEffectiveUri();
//      }])->getHeaders();
//      dump($url);
//      die();

      // If response is not OK => return false
      if ($response->getStatusCode() != 200) {
        \Drupal::logger('opendatasoft')->warning('STATUS CODE != 200 - URL: ' . $request_url . ', QUERY: ' . json_encode($query));
        return FALSE;
      }

      $body = $response->getBody();
      if (empty($body)) {
        return FALSE;
      }
    }
    catch (GuzzleException $e) {
      watchdog_exception('opendatasoft', $e);
      return FALSE;
    }

    return Json::decode($body);
  }

}
