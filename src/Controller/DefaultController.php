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
class DefaultController extends ControllerBase {

  /**
   * Handleautocomplete.
   *
   * @return string
   *   Return Hello string.
   */
  public function handleAutocompleteProject(Request $request) {
    $results = [];

    if ($input = $request->query->get('q')) {
      $typed_string = Tags::explode($input);
      $typed_string = Unicode::strtolower(array_pop($typed_string));

      $manager = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
      $tree = $manager->loadTree( 'project', 0, NULL, TRUE);

      foreach ($tree as $term) {
        if (!$this->getParent($term->id())) {
          # $results[$term->id()] = $term->getName();
          $results[] = $term->getName() . " [" . $term->id() . "]";
        }
      }
    }

    return new JsonResponse($results);
  }

  private function getParent($tid) {
      $parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($tid);
      return reset($parent);
  }

  private function addAncestors($term) {
    $stack = [];
    while ($term) {
      array_unshift($stack, $term->getName());
      $term = $this->getParent($term->id());
    }
    return implode(": ", $stack);
  }

  /**
   * Handleautocomplete.
   *
   * @return string
   *   Return Hello string.
   */
  public function handleAutocompleteActivity(Request $request) {
    $results = [];

    if ($input = $request->query->get('q')) {
      $typed_string = Tags::explode($input);
      $typed_string = Unicode::strtolower(array_pop($typed_string));

      $manager = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
      $tree = $manager->loadTree( 'project', 0, NULL, TRUE);

      foreach ($tree as $term) {
        if (empty($manager->loadChildren($term->id()))) {
          # $results[$term->id()] = $this->addAncestors($term);
          $results[] = $this->addAncestors($term) . " [" . $term->id() . "]";
        }
      }
    }

    return new JsonResponse($results);
  }



}

// vim: set filetype=php expandtab tabstop=2 shiftwidth=2 autoindent smartindent:
