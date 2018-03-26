<?php

namespace Drupal\bigpipetest\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'text_default' formatter.
 *
 * @FieldFormatter(
 *   id = "lazy_text",
 *   label = @Translation("Lazy"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   }
 * )
 */
class LazyTextFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#lazy_builder' => [
          static::class . '::lazyBuilder',
          [$item->value, $item->format, $item->getLangcode()],
        ],
        '#create_placeholder' => TRUE,
      ];
    }

    return $elements;
  }

  public static function lazyBuilder($text, $format, $langcode) {

    $build = [
      '#type' => 'markup',
      '#markup' => $text,
//      '#format' => $format,
//      '#langcode' => $langcode,
    ];

    // In order to demonstrate the use of lazy builders we use sleep here to
    // simulate an expensive request.
    sleep(3);
    return $build;
  }

}
