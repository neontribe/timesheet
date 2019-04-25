<?php

namespace Drupal\timesheet\Controller;

use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class DefaultController.
 */
class DefaultController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;
  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Component\Serialization\SerializationInterface definition.
   *
   * @var \Drupal\Component\Serialization\SerializationInterface
   */
  protected $serializationJson;

  /**
   * Constructs a new DefaultController object.
   */
  public function __construct(EntityManagerInterface $entity_manager, EntityTypeManagerInterface $entity_type_manager, SerializationInterface $serialization_json) {
    $this->entityManager = $entity_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->serializationJson = $serialization_json;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.manager'),
      $container->get('serialization.json')
    );
  }

  /**
   * Byuuid.
   *
   * @return string
   *   Return Hello string.
   */
  public function byuuid($uuid) {
    $nodes = $this->entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['field_issue_uuid' => $uuid]);

    $data = [];
    $serializer = \Drupal::service('serializer');
    foreach ($nodes as $id => $node) {
      $data[$id] = json_decode($serializer->serialize($node, 'json', ['plugin_id' => 'entity']));
    }
    return new JsonResponse($data);
  }

  public function listUsers() {
    $ids = \Drupal::entityQuery('user')
        ->condition('status', 1)
        ->execute();
    $users = User::loadMultiple($ids);
    $data = [];
    foreach ($users as $user) {
      $data[$user->id()] = $user->getDisplayName();
    }

    return new JsonResponse($data);
  }

  public function listProjects() {
    $nids = \Drupal::entityQuery('node')->condition('type','project')->execute();
    $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);

    $data = [];
    foreach ($nodes as $node) {
      $data[$node->id()] = $node->getTitle();
    }

    return new JsonResponse($data);
  }

  public function listActivities() {
    $vid = 'activity_type';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);

    $data = [];
    foreach ($terms as $term) {
      $data[$term->tid] = $term->name;
    }

    return new JsonResponse($data);
  }
}
