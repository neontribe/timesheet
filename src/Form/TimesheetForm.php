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

    // Build JS
    $customers = $this->getTerms('customers');
    $projects = $this->getTerms('project');
    $activities = $this->getTerms('activity_types');
    $tree = [
      'customers' => $customers,
      'project' => $projects,
      'activity_types' => $activities,
      'tree' => [],
    ];
    
    // loop through projects
    $manager = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $allProjects = $manager->loadTree('project', 0, NULL, TRUE);
    foreach ($allProjects as $project) {
      // get customer tid
      $customerId = $project->get('field_customer')[0]->getValue()["target_id"];
      $customerName = $manager->load($customerId)->getName();
      
      if (!isset($tree['tree'][$customerId])) {
        $tree['tree'][$customerId] = [];
      }
      
      $activities = [];
      $_activities = $project->get('field_activity_types');
      foreach ($_activities as $activity) {
        $activities[] = $activity->getValue()["target_id"];
      }
      
      $tree['tree'][$customerId][$project->id()] = $activities;
    }
    
    // Build Form    
    $form['timesheets_description'] = [
      '#type' => 'textarea',
      '#maxlength' => 255,
      '#required' => True,
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
      '#type' => 'select',
      '#title' => $this->t('Customer'),
      '#options' => $this->getTermArray('customers'),
    ];  

    $form['timesheets_project'] = [
      '#type' => 'select',
      '#attributes' => [
        'placeholder' => "Choose customer first",
      ],
      '#title' => $this->t('Project'),
      '#options' => $this->getTermArray('project'),
    ];  

    $form['timesheets_activity_type'] = [
      '#type' => 'select',
      '#attributes' => [
        'placeholder' => "Choose activity first",
      ],
      '#title' => $this->t('Activity Type'),
      '#options' => $this->getTermArray('activity_types'),
    ];  

    $form['timesheets_import']['timesheet_upload_button'] = [
      '#type' => 'submit',
      '#name' => 'timesheet_submit_button',
      '#value' => $this->t('Save'),
    ];

    // kint($tree);
    $form['#attached']['library'][] = 'timesheet/timesheet';
    $form['#attached']['drupalSettings'] = [ 'timesheet' => $tree, ];

    return $form;
  }  

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $_activity_type = $values['timesheets_activity_type'];
    $_project = $values['timesheets_project'];
    $_customer = $values['timesheets_customer'];

    $project = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($_project);
    $customer = $project->get('field_customer');
    $activity_types = $project->get('field_activity_types');

    $pCustomerTid = $customer[0]->get('target_id')->getValue();
    if ($pCustomerTid != $_customer) {
      $form_state->setErrorByName('timesheets_project', t('Project %project is not owned by the the customer %customer'));
    }

    $activity_type_ids = [];
    foreach ($activity_types as $activity_type) {
      $activity_type_ids[] = $activity_type->get('target_id')->getValue();
    }
    if (!in_array($_activity_type, $activity_type_ids)) {
      $form_state->setErrorByName('timesheets_activity_type', t('Activity type %activity_type is not owned by the the project %project'));
    }
  }

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
    $customer = $values['timesheets_customer'];

    dpm([
      'description' => $description,
      'date' => $date,
      'user' => $user,
      'timespent' => $timespent,
      'activity_type' => $activity_type,
      'project' => $project,
      'customer' => $customer,
    ]);
    /*
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
     */
  }
  
  
  private function getTerms($vid) {
    $data = [];
    
    $manager = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $terms = $manager->loadTree($vid, 0, NULL, TRUE);
    foreach ($terms as $term) {
      $id = $term->id();
      $name = $term->getName();
      $data[$id] = $name;
    }
    
    return $data;
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
  
  private function getTermArray($vocab) {
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocab, 0, NULL, TRUE);
    $carray = [];
    
    foreach ($terms as $term) {
      $carray[$term->id()] = sprintf("%s (%d)", $term->getName(), $term->id());
    }
    
    return $carray;
  }
}

// vim: set filetype=php expandtab tabstop=2 shiftwidth=2 autoindent smartindent:

