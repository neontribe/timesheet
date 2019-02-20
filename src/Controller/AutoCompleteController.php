<?php

namespace Drupal\timesheet\Controller;

use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\TermStorage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DefaultController.
 *
 * https://boylesoftware.com/blog/drupal-8-get-taxonomy-terms-level/
 */
class AutoCompleteController extends ControllerBase {

//  /**
//   * Handleautocomplete.
//   *
//   * @return string
//   *   Return Hello string.
//   */
//  public function handleAutocompleteProject(Request $request) {
//    $results = [];
//
//    $customerFromForm = $request->query->get('customer') ?? False;
//    if (!$customerFromForm) {
//      return new JsonResponse([]);
//    }
//
//    $manager = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
//
//    $input = $request->query->get('q');
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
//        if ($fieldCustomerId === $customer_tid) {
//          $results[] = sprintf("%s (%d)", $project->getname(), $project->id());
//        }
//      }
//    }
//
//    return new JsonResponse($results);
//  }
//  
//  private function getTidFromText($text) {
//    preg_match('#\((.*?)\)#', $text, $match);
//    // error_log(json_encode($match));
//    return $match[1];
//  }

}

// vim: set filetype=php expandtab tabstop=2 shiftwidth=2 autoindent smartindent:
