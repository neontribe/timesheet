<?php
namespace Drupal\timesheet\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Class DefaultService.
 */
class DefaultService {

  /**
   * Symfony\Component\DependencyInjection\ContainerAwareInterface definition.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerAwareInterface
   */
  protected $entityQuery;

  /**
   * Drupal\webprofiler\Entity\EntityManagerWrapper definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new DefaultService object.
   */
  public function __construct(ContainerAwareInterface $entity_query, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityQuery = $entity_query;
    $this->entityTypeManager = $entity_type_manager;
  }

  public function byuuid($uuid) {
    $nodes = $this->entityTypeManager->getStorage('node')->loadByProperties([
      'field_issue_uuid' => $uuid
    ]);
    return $nodes;
  }

  public function listUsers() {
    $ids = \Drupal::entityQuery('user')->condition('status', 1)->execute();
    $users = User::loadMultiple($ids);
    return $users;
  }

  public function listProjects() {
    $nids = \Drupal::entityQuery('node')->condition('type', 'project')->execute();
    $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
    return $nodes;
  }

  public function listActivities() {
    $vid = 'activity_type';
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vid);
    return $terms;
  }

  public function getProject($board) {
    // get project
    error_log('searching board ' . $board, E_ERROR);
    $nodes = $this->entityTypeManager->getStorage('node')->loadByProperties([
      'field_project_uuid' => $board
    ]);
    error_log('node count' . count($nodes), E_ERROR);
    $node = \reset($nodes);

    // project does not exists create it
    if (! $node) {
      $node = Node::create([
        'type' => 'project'
      ]);
      $node->set('title', $board);

      $body = [
        'value' => 'Created from ' . \Drupal::request()->server->get('HTTP_REFERER'),
        'format' => 'basic_html'
      ];
      $node->set('body', $body);
      $node->set('field_project_uuid', $board);
      $node->status = 1;
      $node->enforceIsNew();
      $node->save();
    }

    return $node;
  }
}
