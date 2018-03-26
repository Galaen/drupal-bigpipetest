<?php

namespace Drupal\bigpipetest\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'SimpleNoCacheBlock' block.
 *
 * @Block(
 *  id = "simple_no_cache_block",
 *  admin_label = @Translation("Simple no cache block"),
 * )
 */
class SimpleNoCacheBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a new SimpleNoCacheBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct( array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }
  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    //sleep(2);

    $build['text']['#markup'] = 'Simple NO CACHE block: ' . time() . sleep(10)
      . 'sdflgsdflm<br>'
      . 'sdflgsdflm<br>'
      . 'sdflgsdflm<br>'
      . 'sdflgsdflm<br>'
      . 'sdflgsdflm<br>'
      . 'sdflgsdflm<br>'
      . 'sdflgsdflm<br>'
      . 'sdflgsdflm<br>'
      . 'sdflgsdflm<br>'
      . 'sdflgsdflm<br>';
    $build['#cache']['max-age'] = 0;

    return $build;
  }

}
