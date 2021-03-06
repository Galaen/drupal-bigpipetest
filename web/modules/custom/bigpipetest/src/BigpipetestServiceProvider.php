<?php

namespace Drupal\bigpipetest;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

// @note: You only need Reference, if you want to change service arguments.
//use Symfony\Component\DependencyInjection\Reference;

/**
 * Modifies the language manager service.
 */
class BigpipetestServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides language_manager class to test domain language negotiation.
    // Adds entity_type.manager service as an additional argument.
    $definition = $container->getDefinition('big_pipe');
    $definition->setClass('Drupal\bigpipetest\Render\OrderBigPipe');
  }
}