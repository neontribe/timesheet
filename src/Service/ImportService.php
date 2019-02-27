<?php

namespace Drupal\timesheet\Service;

use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

/**
 * Class ImportService.
 */
class ImportService {

  protected $messenger;

  /**
   * Constructs a new ImportService object.
   */
  public function __construct() {
    $this->messenger = \Drupal::messenger();
  }

  public function parseCsv(File $file) {
    $rows = file($file->getFileUri());
    $this->parseRows($rows);
  }

  public function parseRows(array $rows) {
    $messages = [];

    foreach ($rows as $row) {
      $message = $this->parseRow($row);
      if ($message) {
        $messages[] = $message;
      }
    }

    return $messages;
  }

  /**
   * Reads a single line of CSV data and breaks it up into fileds.
   *
   * Those fields then populate a new timesheet object.
   *
   * @param string $row
   * @return string | null
   */
  public function parseRow($row) {
    $messages = [];

    $config = \Drupal::config('timesheet.adminsettings');
    $hashes = $config->get('hashes');
    if (!$hashes) {
      $hashes = [];
    }

    $cols = str_getcsv($row, "\t");
    if (!($cols || is_array($cols))) {
      return 'Unable to parse CSV row: ' . $row;
    }
    $hash = md5(implode('|', $cols));
    if (in_array($hash, $hashes)) {
      return "Row exists: " . $row;
    }
    else {
      if (count($cols) < 7) {
        return 'Invalid row: ' . $row;
      }

      $customer = $this->findCustomer($cols[1]);
      $activity = $this->findActivityType($cols[3]);
      $project = $this->findProject($cols[2], $customer, $activity);

      $date = date('Y-m-d', strtotime($cols[4]));
      $duration = $this->formatTime($cols[5]);
      $minutes = $cols[5];
      $title = $cols[6];
      $user = $this->findUser($cols[7]);

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
      if ($node->save()) {
        $hashes[] = $hash;
      }
      else {
        return 'Failed to save row: ' . $row;
      }
    }

    \Drupal::service('config.factory')
      ->getEditable('timesheet.adminsettings')
      ->set('hashes', $hashes)->save();
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
        'Created %vocab: %term', [
        '%vocab' => $vocab,
        '%term' => $name,
        ]
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