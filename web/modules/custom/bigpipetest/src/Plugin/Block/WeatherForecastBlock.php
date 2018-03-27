<?php

namespace Drupal\bigpipetest\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
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
class WeatherForecastBlock extends BlockBase implements ContainerFactoryPluginInterface, FormInterface {

  /**
   * Drupal\opendatasoft\APIServiceInterface definition.
   *
   * @var \Drupal\opendatasoft\APIServiceInterface
   */
  protected $opendatasoftApi;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

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
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   The session manager.
   */
  public function __construct(
    array $configuration, $plugin_id, $plugin_definition,
    APIServiceInterface $opendatasoft_api, FormBuilderInterface $form_builder, SessionManagerInterface $session_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->opendatasoftApi = $opendatasoft_api;
    $this->formBuilder = $form_builder;
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
      $container->get('form_builder'),
      $container->get('session_manager')
    );
  }


    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration() {
        return [
            'cache_duration' => 30,
            'nosession_cache_duration' => 60,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state) {
        $config = $this->configuration;

        $form['cache_duration'] = [
            '#type' => 'number',
            '#title' => $this->t('Default cache duration (sec)'),
            '#description' => $this->t('The cache duration for users with a session.'),
            '#required' => TRUE,
            '#default_value' => $config['cache_duration'],
            '#step' => 1,
            '#min' => 0,
//            '#max' => static::MAX_DURATION,
        ];
        $form['nosession_cache_duration'] = [
            '#type' => 'number',
            '#title' => $this->t('Sessionless cache duration (ms)'),
            '#description' => $this->t('The cache duration for users without a session.'),
            '#required' => TRUE,
            '#default_value' => $config['nosession_cache_duration'],
            '#step' => 1,
            '#min' => 0,
//            '#max' => static::MAX_DURATION,
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state) {
        $this->configuration['cache_duration'] = $form_state->getValue('cache_duration');
        $this->configuration['nosession_cache_duration'] = $form_state->getValue('nosession_cache_duration');
    }


    /**
   * {@inheritdoc}
   */
  public function build() {
    $sessionStarted = $this->sessionManager->isStarted();

    if ($sessionStarted)
      $cacheDuration = $this->configuration['cache_duration'];
    else
      $cacheDuration = $this->configuration['nosession_cache_duration'];

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

    // FORM
    $build['form'] = $this->formBuilder->getForm($this);

    // CACHE
    $maxAge = \Drupal\Core\Cache\Cache::PERMANENT;
    if (!$sessionStarted) {
      $maxAge = $cacheDuration;   // 1 min cache when there is no session
    }
    $build['#cache'] = [
      'contexts' => ['session.exists'], // 2 cache version (for user with a session and for the others)
      'max-age' => $maxAge,
      'tags' => ['weather_forecast_block'],
    ];

    return $build;
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bigpipetest_weather_forecast_block';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['inv_cache_tag'] = [
      '#type' => 'submit',
      '#value' => $this->t('Invalidate cache tag'),
      '#submit' => ['::invCacheTag'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Starts session.
   */
  public function invCacheTag(array &$form, FormStateInterface $form_state) {
    Cache::invalidateTags(['weather_forecast_block']);
  }

}
