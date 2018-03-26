<?php

namespace Drupal\bigpipetest\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\opendatasoft\APIServiceInterface;
use Drupal\Core\Url;

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
   * Constructs a new WeatherForecastBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param APIServiceInterface $opendatasoft_api
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    APIServiceInterface $opendatasoft_api
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->opendatasoftApi = $opendatasoft_api;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('opendatasoft.api')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['weather_forecast_block']['#markup'] = 'Implement WeatherForecastBlock.';

    $build['lazy_container']['lazy_builder'] = [
      '#lazy_builder' => [
        'bigpipetest.forecast_generator:generateWeatherForecast',
        [],
      ],
      '#create_placeholder' => TRUE,
    ];
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

    return $build;
  }

}
