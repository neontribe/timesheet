<?php

namespace Drupal\timesheet\Controller;

use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DebugController.
 */
class DebugController extends ControllerBase {


//  /**
//   * Test.
//   *
//   * @return string
//   *   Return Hello string.
//   */
//  public function test() {
//    $customers = $this->getTerms('customers');
//    $projects = $this->getTerms('project');
//    $activities = $this->getTerms('activity_types');
//    $tree = [
//      'customers' => $customers,
//      'project' => $projects,
//      'activity_types' => $activities,
//      'tree' => [],
//    ];
//    
//    // loop through projects
//    $manager = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
//    $allProjects = $manager->loadTree('project', 0, NULL, TRUE);
//    foreach ($allProjects as $project) {
//      // get customer tid
//      $customerId = $project->get('field_customer')[0]->getValue()["target_id"];
//      $customerName = $manager->load($customerId)->getName();
//      
//      if (isset($tree['tree'][$customerId])) {
//        $tree['tree'][$customerId] = [];
//      }
//      
//      $activities = [];
//      $_activities = $project->get('field_activity_types');
//      foreach ($_activities as $activity) {
//        $activities[] = $activity->getValue()["target_id"];
//      }
//      
//      $tree['tree'][$customerId][$project->id()] = $activities;
//    }
//      
//    return [
//      '#type' => 'markup',
//      '#markup' => $this->t('Implement method: test'),
//      '#attached' => [
//        'drupalSettings' => [ 'timesheet' => $tree, ],
//      ],
//    ];
//  }
//  
//  private function getTerms($vid) {
//    $data = [];
//    
//    $manager = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
//    $terms = $manager->loadTree($vid, 0, NULL, TRUE);
//    foreach ($terms as $term) {
//      $id = $term->id();
//      $name = $term->getName();
//      $data[$id] = $name;
//    }
//    
//    return $data;
//  }
//  
//  function foo() {
//    $results = [];
//
//    // $customerFromForm = $request->query->get('customer') ?? False;
//    $customerFromForm = "Neontribe (395)";
//    if (!$customerFromForm) {
//      return new JsonResponse([]);
//    }
//
//    $manager = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
//
//    // $input = $request->query->get('q');
//    $input = "zzz";
//    if ($input) {
//      $customer_tid = $this->getTidFromText($customerFromForm);      
//      dpm("Cust:  " . $customer_tid);
//
//      $typed_string = Tags::explode($input);
//      $typed_string = Unicode::strtolower(array_pop($typed_string));
//
//      $allProjects = $manager->loadTree('project', 0, NULL, TRUE);
//      
//      foreach ($allProjects as $project) {
//        $fieldCustomerId = $project->get('field_customer')[0]->getValue()["target_id"];
//        dpm($fieldCustomerId);
//      }
//    }
//      
//    return [
//      '#type' => 'markup',
//      '#markup' => $this->t('Implement method: test')
//    ];
//  }
//
//  private function getTidFromText($text) {
//    preg_match('#\((.*?)\)#', $text, $match);
//    // error_log(json_encode($match));
//    return $match[1];
//  }
//  
//  ///////////////////////////////////////////
//  
//  private function getParent($tid) {
//    $parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($tid);
//    return reset($parent);
//  }
//
//  private function addAncestors($term) {
//    $stack = [];
//    while ($term) {
//      array_unshift($stack, $term->getName());
//      $term = $this->getParent($term->id());
//    }
//    return implode(": ", $stack);
//  }
//
//  /**
//   * Handleautocomplete.
//   *
//   * @return string
//   *   Return Hello string.
//   */
//  public function handleAutocompleteActivity(Request $request) {
//    $results = [];
//
//    if ($input = $request->query->get('q')) {
//      $typed_string = Tags::explode($input);
//      $typed_string = Unicode::strtolower(array_pop($typed_string));
//
//      $manager = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
//      $tree = $manager->loadTree('project', 0, NULL, TRUE);
//
//      foreach ($tree as $term) {
//        if (empty($manager->loadChildren($term->id()))) {
//          # $results[$term->id()] = $this->addAncestors($term);
//          $results[] = $this->addAncestors($term) . " [" . $term->id() . "]";
//        }
//      }
//    }
//
//    return new JsonResponse($results);
//  }

}
