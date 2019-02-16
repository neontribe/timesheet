<?php

/**  
 * @file  
 * Contains Drupal\timesheet\Form\TimesheetForm.  
 */  
namespace Drupal\timesheet\Form;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;

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

    $form['timesheets_description'] = [
      '#type' => 'textarea',
      '#maxlength' => 255,
      '#title' => $this->t('Description of activity'),
    ];  

    $form['timesheets_user'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('User'),
      '#default_value' => User::load(\Drupal::currentUser()->id()),
    ];  

    $form['timesheets_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Date of activity'),
      '#default_value' => date('Y-m-d'),
    ];  

    $form['timesheets_timespent'] = [
      '#type' => 'duration',
      '#title' => $this->t('Time spent'),
      '#granularity' => 'h:i',
    ];  

    $form['timesheets_customer'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'taxonomy_term',
      '#attributes' => [
        'placeholder' => "Choose customer",
      ],
      '#selection_settings' => [
        'target_bundles' => ['customers'],
      ],
      '#title' => $this->t('Customer'),
    ];  

    $form['timesheets_project'] = [
      '#type' => 'textfield',
      '#autocomplete_route_name' => 'timesheet.autocomplete_project',
      '#attributes' => [
        'placeholder' => "Choose customer first",
      ],
      '#title' => $this->t('Project'),
    ];  

    $form['timesheets_activity_type'] = [
      '#type' => 'textfield',
      '#autocomplete_route_name' => 'timesheet.autocomplete_activity',
      '#attributes' => [
        'placeholder' => "Choose activity first",
      ],
      '#title' => $this->t('Activity Type'),
    ];  

    $form['timesheets_import']['timesheet_upload_button'] = [
      '#type' => 'submit',
      '#name' => 'timesheet_submit_button',
      '#value' => $this->t('Save'),
    ];


    $form['#attached']['library'][] = 'timesheet/timesheet';

    return $form;
  }  

  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $description = $values['timesheets_description'];
    $date = $values['timesheets_date'];
    $user = $values['timesheets_user'];
    $timespent = $values['timesheets_timespent'];
    $activity_type = $values['timesheets_activity_type'];
    $project = $values['timesheets_project'];

    $title = sprintf("%s %s", $activity_type, $date);

    $node = Node::create([
      'type'  => 'timesheet_entry',
      'title' => $title,
      'body'  => $description,
    ]);
    $node->set('field_date', $date);
    $node->set('field_user', $user);
    $node->set('field_time_spent', $timespent);
    $node->set('field_activity_type', $this->getTermFromBrackets($activity_type));
    $node->set('field_project', $this->getTermFromBrackets($project));
    $node->save();
  }

  private function getTermFromBrackets($text) {
    $stack = explode("[", $text);
    $trailing = array_pop($stack);
    
    if (!$trailing) {
      return False;
    }

    $tid = trim($trailing, "[] ");
    $term = Term::load($tid);
    return $term;
  }
}

// vim: set filetype=php expandtab tabstop=2 shiftwidth=2 autoindent smartindent:

