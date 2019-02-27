<?php

namespace Drupal\timesheet\Form\Admin;

use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Datetime\Element\Datetime;

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
    // $hashes = $config->get('hashes');
    if (!$hashes) {
      $hashes = [];
    }

    foreach ($rows as $row) {
      $cols = str_getcsv($row, "\t");
      if (!($cols || is_array($cols))) {
        drupal_set_message('Unable to parse CSV row: ' . $row);
        continue;
      }
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

        $customer = $this->findCustomer($cols[1]);
        $activity = $this->findActivityType($cols[3]);
        $project = $this->findProject($cols[2], $customer, $activity);

        $date = date('Y-m-d', strtotime($cols[4]));
        $duration = $this->formatTime($cols[5]);
        $minutes = $cols[5];
        $title = $cols[6];
        $user = $this->findUser($cols[7]);
        // dpm($user ? $user->getName() : False, 'User');

        $node = Node::create([
            'type' => 'timesheet_entry',
            'title' => $title,
        ]);
        $node->set('field_date', $date);
        $node->set('field_time_spent', $duration);
        $node->set('field_duration_minutes_', $minutes);
        $node->set('field_user', $user);
        $node->set('field_project', $project);
        $node->set('field_activity_type', $activity);
        $node->save();
      }
    }

    return $duplicates;
  }

  private function findUser($user) {
    $users = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->loadByProperties([
      'name' => $user,
    ]);
    if (count($users)) {
      $user = array_pop($users);
      return $user;
    }
    else {
      return False;
    }
  }

  private function formatTime($minutes) {
    return gmdate('\P\TH\Hi\M', $minutes);
  }

  /**
   * Wrapper for findTerm($customer, 'customers').
   *
   * @param string $customer The customer to search for.
   *
   * @return Drupal\taxonomy\Entity\Term
   */
  private function findCustomer($customer): Term {
    $term = $this->findTerm($customer, 'customers');

    return $term;
  }

  /**
   * Wrapper for findTerm($activity, 'activity_types').
   *
   * @param string $activity The activity_type to search for.
   *
   * @return Drupal\taxonomy\Entity\Term
   */
  private function findActivityType($activity): Term {
    $term = $this->findTerm($activity, 'activity_types');

    return $term;
  }

  /**
   * Find or create a new project.
   *
   * @param type $project                         The name of the project.
   * @param Drupal\taxonomy\Entity\Term $customer The customer term.
   * @param Drupal\taxonomy\Entity\Term $activity The activity term.
   *
   * @return Drupal\taxonomy\Entity\Term
   */
  private function findProject($project, Term $customer, Term $activity) {
    $term = $this->findTerm($project, 'project');

    // Set the customer on this project.
    $term->set('field_customer', $customer);
    // Add the activity type, this seems to have a implicit no dupes
    $existing_activities = [];
    foreach ($term->field_activity_types as $activty_type) {
      $existing_activities[] = $activty_type->getEntity()->getName();
    }
    if (!in_array($activity->getName(), $existing_activities)) {
      $term->field_activity_types->appendItem($activity);
    }
    $term->save();

    return $term;
  }

  /**
   * Find a term in the specified vocab, or create it if it does not exist.
   *
   * @param string $name  The name of the term to find.
   * @param string $vocab The vocab to search in.
   *
   * @return Drupal\taxonomy\Entity\Term
   */
  private function findTerm($name, $vocab): Term {
    $term = false;

    $terms = taxonomy_term_load_multiple_by_name($name, $vocab);
    if (count($terms)) {
      // There should only ever be one
      $term = array_shift($terms);
    }
    else {
      // Create the term
      $term = Term::create(['name' => $name, 'vid' => $vocab]);
      $term->save();
      drupal_set_message(
        $this->t(
          'Created %vocab: %term', [
          '%vocab' => $vocab,
          '%term' => $name,
          ]
        )
      );
    }
    /*
      $term = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties([
      'name' => $term_name,
      ]);
     */
    return $term;
  }

}

// vim: set filetype=php expandtab tabstop=2 shiftwidth=2 autoindent smartindent:

