<?php

namespace Drupal\bigpipetest\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Url;
use Drupal\opendatasoft\APIServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'WeatherForecastBlock' block.
 *
 * @Block(
 *  id = "weather_forecast_block",
 *  admin_label = @Translation("Weather forecast block"),
 * )
 */
class WeatherForecastBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * Constructs a new WeatherForecastBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param APIServiceInterface $opendatasoft_api
   *   Service to abstract OpenDataSoft API.
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   The session manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    APIServiceInterface $opendatasoft_api, SessionManagerInterface $session_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->opendatasoftApi = $opendatasoft_api;
    $this->sessionManager = $session_manager;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('opendatasoft.api'),
      $container->get('session_manager')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function build() {
    $sessionStarted = $this->sessionManager->isStarted();
    $cacheDuration = 60;
    if ($sessionStarted)
      $cacheDuration = 30;

    $build = [];
    $build['weather_forecast_block']['#markup'] = $this->t('<p>Displays the next few hours weather forecast.</p>');

    if ($sessionStarted) {
      $build['lazy_container']['lazy_builder'] = [
        '#lazy_builder' => [
          'bigpipetest.forecast_generator:generateWeatherForecast',
          [$sessionStarted, $cacheDuration],
        ],
        '#create_placeholder' => TRUE,
      ];
    }
    else {
      $build['lazy_container']['table'] = \Drupal::service('bigpipetest.forecast_generator')->generateWeatherForecast(FALSE, $cacheDuration);
    }
    $build['source'] = [
      '#type' => 'link',
      '#title' => t('Source'),
      '#prefix' => '<small>',
      '#suffix' => '</small>',
      '#url' => Url::fromUri('https://public.opendatasoft.com/explore/dataset/arome-0025-sp1_sp2_paris/information/'),
      '#options' =>[
        'attributes' => [
          'target' => '_blank'
        ]
      ]
    ];

    $maxAge = \Drupal\Core\Cache\Cache::PERMANENT;
    if (!$sessionStarted) {
      $maxAge = $cacheDuration;   // 1 min cache when there is no session
    }
    $build['#cache'] = [
      'contexts' => ['session.exists'], // 2 cache version (for user with a session and for the others)
      'max-age' => $maxAge
    ];

    return $build;
  }

}
