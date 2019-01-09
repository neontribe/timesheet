<?php

/**  
 * @file  
 * Contains Drupal\timesheet\Form\Admin\TimesheetForm.  
 */  
namespace Drupal\timesheet\Form\Admin;

use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;
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
  }

  private function parseUploadedCsv(File $file) {
    $rows = file($file->getFileUri());
    
    $duplicates = [];
    $importable = [];

    $config = $this->config('timesheet.adminsettings');
    $hashes = $config->get('hashes', []);

    foreach ($rows as $row) {
      $cols = str_getcsv($row, ",", '"'); //parse the items in rows 
      $hash = md5(implode('|', $cols));
      if (in_array($hash, $hashes)) {
        $duplicates[$hash] = $cols;
      }
      else {
        if (count($cols) < 7) {
          drupal_set_message('Invalid row: ' . $row);
          continue;
        }
        $importable[$hash] = $cols;
        $user = $this->findUser($cols[0]);
        $title = $cols[1];
        $date = $cols[2];
        $time = $this->formatTime($cols[3]);
        $desc = $cols[4];
        $activity = $this->findTerm($cols[5], 'billing_type');
        $project = $this->findTerm($cols[6], 'project');
        // $node = Node::create([
          // 'type'        => 'timesheet_entry',
          // 'title'       => $cols[2],
          // 'body'        => $cols[5],
        // ]);
        // $node->set('field_time_spent', gmdate('\P\TH\Hi\M', $cols[3]));
        // $node->set('field_user', $user);
        // $node->set('field_billing_type', $billing);
        // $node->set('field_project', $project);
        // $node->save();
      }
    }

    return $duplicates;
    # drupal_set_message($data);
  }

  private function findUser($user) {
  }

  private function formatTime($seconds) {
    return gmdate('\P\TH\Hi\M', $seconds/60);
  }

  private function findTerm($name, $vocab) {
    $term = false;

    $terms = taxonomy_term_load_multiple_by_name($name, $vocab);
    if (count($terms)) {
      // There should only ever be one
      $term = $terms[0];
    }
    else {
      // Create the term
      $term = Term::create([
        'name' => $name,
        'vid' => $vocab,
      ])->save();
    }
    #$term = \Drupal::entityTypeManager()
      #->getStorage('taxonomy_term')
      #->loadByProperties([
        #'name' => $term_name,
    #]);
    return $term;
  }
}

// vim: set filetype=php expandtab tabstop=2 shiftwidth=2 autoindent smartindent:

