<?php

namespace Drupal\timesheet\Form\Admin;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

/**
 * TODO: move the import into a service and add a cli wrapper/batch process.
 */
class TimesheetForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'timesheet.adminsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'timesheet_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('timesheet.adminsettings');

    $form['timesheets_import'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Import timesheets'),
    ];

    $form['timesheets_import']['timesheet_blocksize'] = [
      '#type' => 'number',
      '#title' => $this->t('Block size'),
      '#default_value' => 10,
      '#description' => $this->t('The number of rows to process in each chunk.'),
    ];

    $form['timesheets_import']['timesheet_upload_csv'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload CSV'),
      '#upload_location' => 'public://timesheets',
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
      ],
      '#description' => $this->t('Upload record extracted from another time system.  The CSV should comma seperated, quote enclosed fields.  The fields shouls be id, user, title, starttime, duration in seconds, description, project name, and compoany name.'),
    ];

    $form['timesheets_import']['timesheet_upload_button'] = [
      '#type' => 'submit',
      '#name' => 'timesheet_upload_button',
      '#value' => $this->t('Upload'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $triggering_element = $form_state->getTriggeringElement();
    $button_name = $triggering_element['#name'];
    if ($button_name === 'timesheet_upload_button') {
      $form_file = $form_state->getValue('timesheet_upload_csv', 0);
      $blocksize = $form_state->getValue('timesheet_blocksize');
      if (isset($form_file[0]) && !empty($form_file[0])) {
        $file = File::load($form_file[0]);

        $batch = array(
          'title' => t('Importing timesheet data...'),
          'operations' => array(
            array(
              '\Drupal\timesheet\Batch\ImportFromCSV::batchOp',
              array($file->getFileUri(), $blocksize)
            ),
          ),
          'finished' => '\Drupal\timesheet\Batch\ImportFromCSV::importRowsFinishedCallback',
          'init_message' => t('Initialsing timesheet data import.'),
          'progress_message' => t('Processed @current out of @total.'),
          'error_message' => t('Timesheet import has encountered an error.'),
        );
        batch_set($batch);
      }
    }
    else {
      parent::submitForm($form, $form_state);
    }
  }

}

// vim: set filetype=php expandtab tabstop=2 shiftwidth=2 autoindent smartindent: