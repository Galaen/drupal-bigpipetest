<?php

namespace Drupal\bigpipetest\Render;

use Drupal\big_pipe\Render\BigPipe;

class OrderBigPipe extends BigPipe {

  protected function getPlaceholderOrder($html, $placeholders) {
    //return parent::getPlaceholderOrder($html, $placeholders);
    //return array_reverse(parent::getPlaceholderOrder($html, $placeholders));

    $ret = parent::getPlaceholderOrder($html, $placeholders);

    // Special case for our element
    $specialId = [];
    foreach ($placeholders as $placeholder_id => $placeholder_element) {
      if (isset($placeholder_element['#lazy_builder']) && $placeholder_element['#lazy_builder'][0] === 'Drupal\bigpipetest\Controller\BigPipeTestController::lazyBuilderCallback') {
        $specialId[] = $placeholder_id;
      }
    }


    // Return placeholder IDs in DOM order, but with the 'special'
    // placeholders at the end, if they are present.
    $ordered_placeholder_ids = array_merge(
      array_diff($ret, $specialId),
      array_intersect($ret, $specialId)
    );

    return $ordered_placeholder_ids;

  }
}