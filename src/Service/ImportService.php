<?php
namespace Drupal\timesheet\Service;

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

  /**
   * Reads a single line of CSV data and breaks it up into fileds.
   *
   * Those fields then populate a new timesheet object.
   *
   * @param string $row
   * @return string | null
   */
  public function parseRow($row) {
    $config = \Drupal::config('timesheet.adminsettings');
    $hashes = $config->get('hashes');
    if (! $hashes) {
      $hashes = [];
    }

    $cols = str_getcsv($row, "\t");
    if (! ($cols || is_array($cols))) {
      return 'Unable to parse CSV row: ' . $row;
    }
    // Remove the index before hashing
    array_splice($cols, 0, 1);
    $hash = \md5(implode('|', $cols));
    if (in_array($hash, $hashes)) {
      return "Row exists: " . $row;
    } else {
      if (\count($cols) < 6) {
        return 'Invalid row: ' . $row;
      }

      $customer = $this->findCustomer($cols[0]);
      $activity = $this->findActivityType($cols[2]);
      $project = $this->findProject($cols[1], $customer);

      $date = \date('Y-m-d', strtotime($cols[3]));
      $minutes = $cols[4];
      $title = $cols[5];
      $user = $this->findUser($cols[6]);

      $node = Node::create([
        'type' => 'time_sheet_entry',
        'title' => $title
      ]);

      $node->set('field_activity_type', $activity);
      $node->set('field_date', $date);
      $node->set('field_duration', $minutes);
      $node->set('field_project', $project);
      $node->set('field_user', $user);

      if ($node->save()) {
        $hashes[] = $hash;
      } else {
        return 'Failed to save row: ' . $row;
      }
    }

    \Drupal::service('config.factory')->getEditable('timesheet.adminsettings')
      ->set('hashes', $hashes)
      ->save();
  }

  public function findUser($user) {
    $filter = [
      'name' => $user
    ];

    $config = \Drupal::configFactory()->get('timesheet.adminconfig');
    $user_rows = explode("\n", $config->get('users'));
    foreach ($user_rows as $row) {
      if (strpos($row, $user)) {
        list ($key, $val) = explode("|", $row);
        if ($val) {
          $filter = [
            'name' => $val
          ];
          break;
        }
      }
    }

    $users = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties($filter);
    if (\count($users)) {
      $user = array_pop($users);
      return $user;
    } else {
      return False;
    }
  }

  /**
   * Wrapper for findTerm($customer, 'customers').
   *
   * @param string $customer
   *          The customer to search for.
   *
   * @return \Drupal\taxonomy\Entity\Term
   */
  public function findCustomer($customer): Node {
    $nids = \Drupal::entityQuery('node')->condition('type', 'customer')
      ->condition('title', $customer)
      ->execute();

    if (\count($nids)) {
      $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
      return \reset($nodes);
    }

    // There is no customer create one.
    $node = Node::create([
      'type' => 'customer'
    ]);
    $node->set('title', $customer);

    $node->status = 1;
    $node->enforceIsNew();
    $node->save();

    return $node;
  }

  /**
   * Wrapper for findTerm($activity, 'activity_types').
   *
   * @param string $activity
   *          The activity_type to search for.
   *
   * @return \Drupal\taxonomy\Entity\Term
   */
  public function findActivityType($activity): Term {
    $term = false;
    $vocab = 'activity_type';

    $terms = taxonomy_term_load_multiple_by_name($activity, $vocab);
    if (\count($terms)) {
      // There should only ever be one
      $term = array_shift($terms);
    } else {
      // Create the term
      $term = Term::create([
        'name' => $activity,
        'vid' => $vocab
      ]);
      $term->save();
      \Drupal::logger('timesheet')->info(sprintf('Created %s: %s', $vocab, $activity));
    }
    return $term;
  }

  /**
   * Find or create a new project.
   *
   * @param string $project
   *          The name of the project.
   * @param \Drupal\taxonomy\Entity\Term $customer
   *          The customer term.
   * @param \Drupal\taxonomy\Entity\Term $activity
   *          The activity term.
   *
   * @return \Drupal\taxonomy\Entity\Term
   */
  public function findProject($project, Node $customer): Node {
    $nids = \Drupal::entityQuery('node')->condition('type', 'project')
      ->condition('title', $project)
      ->execute();

    if (\count($nids)) {
      $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
      return \reset($nodes);
    }

    // There is no project create one.
    $node = Node::create([
      'type' => 'project'
    ]);
    $node->set('title', $project);
    $node->set('field_customer', $customer);

    $uuid = $this->getProjectUUID($project);
    if ($uuid) {
      $node->set('field_project_uuid', $uuid);
      \Drupal::logger('timesheet')->warning(sprintf('Creating project %s [%s], Customer: %s', $project, $uuid, $customer->getTitle()));
    } else {
      \Drupal::logger('timesheet')->warning(sprintf('Creating project with no UUID for %s, Customer: %s', $project, $customer->getTitle()));
    }

    $node->status = 1;
    $node->enforceIsNew();
    $node->save();

    return $node;
  }

  public function getProjectUUID($project) {
    $config = \Drupal::configFactory()->get('timesheet.adminconfig');
    $allprojects = \explode("\n", $config->get('projects', []));

    foreach ($allprojects as $_project) {
      $parts = \explode("|", $_project);
      if (\count($parts) > 1 && $parts[0] == $project) {
        return $parts[1];
      }
    }

    // We can't find a matching UUID
    return False;
  }
}















// vim: set filetype=php expandtab tabstop=2 shiftwidth=2 autoindent smartindent: