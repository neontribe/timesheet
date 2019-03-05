<?php

/**
 * @file
 * Contains Drupal\timesheet\Form\TimesheetForm.
 */
namespace Drupal\timesheet\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

class TimesheetForm extends FormBase {

  /**
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'timesheet_add_form';
  }

  /**
   *
   * {@inheritdoc} $this->messenger->addMessage("Row exists: " . $row, MessengerInterface::TYPE_WARNING);
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $timesheet_service = \Drupal::service('timesheet.helper');

    // Build JS
    // $customers = $this->getTerms('customers');
    // $projects = $this->getTerms('project');
    // $activities = $this->getTerms('activity_types');
    $customers = $timesheet_service->getCustomers();
    $projects = $timesheet_service->getProjects();
    $activities = $timesheet_service->getActivityTypes();
    $tree = [
      'customers' => $customers,
      'project' => $projects,
      'activity_types' => $activities,
      'tree' => []
    ];

    // loop through projects
    $manager = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $allProjects = $manager->loadTree('project', 0, NULL, TRUE);
    foreach ($allProjects as $project) {
      // get customer tid
      $customerId = $project->get('field_customer')[0]->getValue()["target_id"];

      if (! isset($tree['tree'][$customerId])) {
        $tree['tree'][$customerId] = [];
      }

      $activities = [];
      $_activities = $project->get('field_activity_types');
      foreach ($_activities as $activity) {
        $activities[] = $activity->getValue()["target_id"];
      }

      $tree['tree'][$customerId][$project->id()] = array_unique($activities);
    }

    // Build Form
    $form['timesheets_description'] = [
      '#type' => 'textarea',
      '#maxlength' => 255,
      '#required' => True,
      '#title' => $this->t('Description of activity')
    ];

    $form['timesheets_user'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('User'),
      '#default_value' => User::load(\Drupal::currentUser()->id())
    ];

    $form['timesheets_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Date of activity'),
      '#default_value' => date('Y-m-d')
    ];

    $form['timesheets_timespent'] = [
      '#type' => 'duration',
      '#title' => $this->t('Time spent'),
      '#granularity' => 'h:i'
    ];

    $form['timesheets_customer'] = [
      '#type' => 'select',
      '#title' => $this->t('Customer'),
      '#options' => $customers
    ];

    $form['timesheets_project'] = [
      '#type' => 'select',
      '#attributes' => [
        'placeholder' => "Choose customer first"
      ],
      '#title' => $this->t('Project'),
      '#options' => $projects
    ];

    $form['timesheets_activity_type'] = [
      '#type' => 'select',
      '#attributes' => [
        'placeholder' => "Choose activity first"
      ],
      '#title' => $this->t('Activity Type'),
      '#options' => $activities
    ];

    $form['timesheets_import']['timesheet_upload_button'] = [
      '#type' => 'submit',
      '#name' => 'timesheet_submit_button',
      '#value' => $this->t('Save')
    ];

    // kint($tree);
    $form['#attached']['library'][] = 'timesheet/timesheet';
    $form['#attached']['drupalSettings'] = [
      'timesheet' => $tree
    ];

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
      $form_state->setErrorByName('timesheets_project', $this->t('Project %project is not owned by the the customer %customer'));
    }

    $activity_type_ids = [];
    foreach ($activity_types as $activity_type) {
      $activity_type_ids[] = $activity_type->get('target_id')->getValue();
    }
    if (! in_array($_activity_type, $activity_type_ids)) {
      $form_state->setErrorByName('timesheets_activity_type', $this->t('Activity type %activity_type is not owned by the the project %project'));
    }
  }

  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $title = $values['timesheets_description'];
    $date = $values['timesheets_date'];
    $user = $values['timesheets_user'];
    $timespent = $values['timesheets_timespent'];
    $activity_type = $values['timesheets_activity_type'];
    $project = $values['timesheets_project'];
    $customer = $values['timesheets_customer'];

    $node = Node::create([
      'type' => 'timesheet_entry',
      'title' => $title
    ]);
    $node->set('field_date', $date);
    $node->set('field_user', $user);
    $node->set('field_time_spent', $timespent);
    $node->set('field_activity_type', $activity_type);
    $node->set('field_project', $project);
    $node->save();
  }
}

// vim: set filetype=php expandtab tabstop=2 shiftwidth=2 autoindent smartindent:

