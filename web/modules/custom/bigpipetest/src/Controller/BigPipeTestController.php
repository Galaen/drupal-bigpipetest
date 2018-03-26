<?php

namespace Drupal\bigpipetest\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BigPipeTestController extends ControllerBase {


    /**
     * Current user.
     *
     * @var \Drupal\user\UserInterface
     */
    protected $currentUser;

    /**
     * Constructs a new BigPipeTestController object.
     *
     * @param \Drupal\Core\Session\AccountInterface $current_user
     *   The current user.
     */
    public function __construct(AccountInterface $current_user) {
        $this->currentUser = $current_user;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('current_user')
        );
    }

    /**
     * Content using a lazy builder and other render arrays.
     */
    public function render() {
      $build = [];

      // NON Lazy element to display before the lazy element
      $build['before_lazy'] = [
        '#markup' => '<p>' . \Drupal::translation()->translate('Before lazy') . '</p>',
      ];

      // NON Lazy element to display before the lazy element
      $build['description'] = [
        '#markup' => '<p>' . \Drupal::translation()->translate('This controller has a lazy part that will be rendered last even if it is before in the DOM as <strong>getPlaceholderOrder</strong> from the big_pipe service has been overridden.') . '</p>',
      ];

      // Lazy element
      $build['lazy_container'] = array(
        '#type' => 'container',
        '#attributes' => array(
          'class' => ['lazy-container'],
        ),
      );

      $build['lazy_container']['lazy_builder'] = [
        '#lazy_builder' => [
          static::class . '::lazyBuilderCallback',
          [$this->currentUser->id()],
        ],
        '#create_placeholder' => TRUE,
      ];

      $build['lazy_container']['loading'] = [
        '#prefix' => '<div class="lazy-loading">',
        '#suffix' => '</div>',
        '#markup' => '<p>' . \Drupal::translation()->translate('Loading please wait...') . '</p>',
      ];

      // NON Lazy element to display after the lazy element
      $build['after_lazy'] = [
        '#markup' => '<p>' . \Drupal::translation()->translate('After lazy') . '</p>'
      ];

      return $build;
    }

    /**
     * Lazy builder callback
     *
     * @param int $user_id
     *   Current user id.
     *
     * @return array
     *   A render array.
     */
    public static function lazyBuilderCallback($user_id) {
      $translation = \Drupal::translation();

      $account = User::load($user_id);

      // Simulate long request
      sleep(1);

      $build = [];
      $build['name'] = [
        '#prefix' => '<div class="lazy-loaded">',
        '#suffix' => '</div>',
        '#markup' => '<p>' . $translation->translate('Hello @name', ['@name' => $account->getDisplayName()]) . '</p><br>'
          . '<small>' . $translation->translate('It is @time', ['@time' => \Drupal::service('date.formatter')->format(REQUEST_TIME, 'custom', 'H:i:s')]) . '</small>',
      ];

      return $build;
    }

}