<?php

/**  
 * @file  
 * Contains Drupal\timesheet\Form\TimesheetForm.  
 */  
namespace Drupal\timesheet\Form;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class TimesheetForm extends FormBase {
  /**  
   * {@inheritdoc}  
   */  
  public function getFormId() {  
    return 'timesheet_add_form';  
  }  

  /**  
   * {@inheritdoc}  
   */  
  public function buildForm(array $form, FormStateInterface $form_state) {  
    $form['timesheets_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
    ];

    $form['timesheets_description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description of activity'),
    ];  

    $form['timesheets_user'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('User'),
      '#default_value' => User::load(\Drupal::currentUser()->id()),
    ];  

    $form['timesheets_timespent'] = [
      '#type' => 'duration',
      '#title' => $this->t('Time spent'),
      '#granularity' => 'h:i',
    ];  

    $form['timesheets_billing'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'taxonomy_term',
      '#selection_settings' => array(
        'target_bundles' => array('billing_type'),
      ),
      '#title' => $this->t('Billing type'),
    ];  

    $form['timesheets_project'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'taxonomy_term',
      '#selection_settings' => array(
        'target_bundles' => array('project'),
      ),
      '#title' => $this->t('Project'),
    ];  

    $form['timesheets_import']['timesheet_upload_button'] = [
      '#type' => 'submit',
      '#name' => 'timesheet_submit_button',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }  

  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $title = $values['timesheets_title'];
    $description = $values['timesheets_description'];
    $user = $values['timesheets_user'];
    $timespent = $values['timesheets_timespent'];
    $billing = $values['timesheets_billing'];
    $project = $values['timesheets_project'];

    $node = Node::create([
      'type'  => 'timesheet_entry',
      'title' => $title,
      'body'  => $description,
    ]);
    $node->set('field_user', $user);
    $node->set('field_time_spent', $timespent);
    $node->set('field_billing_type', $billing);
    $node->set('field_project', $project);
    $node->save();
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

