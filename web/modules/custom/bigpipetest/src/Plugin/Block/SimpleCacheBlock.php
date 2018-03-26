<?php

namespace Drupal\bigpipetest\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'SimpleTestCacheBlock' block.
 *
 * @Block(
 *  id = "simple_cache_block",
 *  admin_label = @Translation("Simple test cache block"),
 * )
 */
class SimpleCacheBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a new SimpleCacheBlock object.
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

    $build['text']['#markup'] = 'Simple block with 60sec cache: ' . time();
    $build['#cache']['max-age'] = 60;

    return $build;
  }

}
