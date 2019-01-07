<?php

/**  
 * @file  
 * Contains Drupal\timesheet\Form\Admin\TimesheetForm.  
 */  
namespace Drupal\timesheet\Form\Admin;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

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
    parent::submitForm($form, $form_state);

    $triggering_element = $form_state->getTriggeringElement();
    $button_name = $triggering_element['#name'];
    if ($button_name === 'timesheet_upload_button') {
      $form_file = $form_state->getValue('timesheet_upload_csv', 0);
      if (isset($form_file[0]) && !empty($form_file[0])) {
        $file = File::load($form_file[0]);
        $this->parseUploadedCsv($file);
      }
    }

    # $this->config('timesheet.adminsettings')
      # ->set('timesheet_upload_records', $form_state->getValue('timesheet_upload_records'))
      # ->save();
  }

  private function parseUploadedCsv(File $file) {
    $rows = file($file->getFileUri());
    
    $duplicates = [];
    $importable = [];

    $config = $this->config('timesheet.adminsettings');
    $hashes = $config->get('hashes');

    foreach ($rows as $row) {
      $cols = str_getcsv($row, ",", '"'); //parse the items in rows 
      $hash = md5(implode('|', $cols));
      if (in_array($hash, $hashes)) {
        $duplicates[$hash] = $cols;
      }
      else {
        $importable[$hash] = $cols;
        $node = Node::create([
          'type'        => 'timesheet_entry',
          'title'       => $cols[2],
        ]);
        $node->set('body', $cols[5]);
        $node->set('field_time_spent', gmdate('\P\TH\Hi\M', 685));
          # 'field_billing_type' => null,
          # 'field_project' => null,
          # 'field_user' => null,
        $node->save();
      }

      return $duplicates;
    }
    # drupal_set_message($data);
  }

}

// vim: set filetype=php expandtab tabstop=2 shiftwidth=2 autoindent smartindent:

