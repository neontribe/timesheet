<?php

namespace Drupal\timesheet\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'AddBlock' block.
 *
 * @Block(
 *  id = "timesheet_add_block",
 *  admin_label = @Translation("Add Time Sheet"),
 * )
 */
class AddBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $node = \Drupal::entityTypeManager()->getStorage('node')->create([
      'type' => 'time_sheet_entry'
    ]);

    $form = \Drupal::service('entity.form_builder')->getForm($node);
    $form['#attached']['library'][] = 'timesheet/timesheet';

    $build = [];
    $build['content'] = $form;



    return $build;
  }

}
