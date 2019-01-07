<?php

/**
 * @file
 * Contains \Drupal\timesheet\Plugin\Block\LogTime.
 */
namespace Drupal\timesheet\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;

/**
 * Provides a 'logtime' block.
 *
 * @Block(
 *   id = "logtime_block",
 *   admin_label = @Translation("Log Time"),
 *   category = @Translation("Timesheet")
 * )
 */
class LogTime extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\timesheet\Form\TimesheetForm');
    return $form;
   }
}
