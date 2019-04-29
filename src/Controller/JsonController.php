<?php
namespace Drupal\timesheet\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\timesheet\Service\DefaultService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class JsonController.
 */
class JsonController extends ControllerBase {

  /**
   * Drupal\timesheet\Service\DefaultService definition.
   *
   * @var \Drupal\timesheet\Service\DefaultService
   */
  protected $timesheetDefault;

  /**
   * Symfony\Component\Serializer\SerializerInterface definition.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * Constructs a new JsonController object.
   */
  public function __construct(DefaultService $timesheet_default, SerializerInterface $serializer) {
    $this->timesheetDefault = $timesheet_default;
    $this->serializer = $serializer;
  }

  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('timesheet.default'), $container->get('serializer'));
  }

  /**
   * By uuid.
   */
  public function byuuid($uuid) {
    $nodes = $this->timesheetDefault->byuuid($uuid);
    $data = [];
    foreach ($nodes as $id => $node) {
      $data[$id] = json_decode($this->serializer->serialize($node, 'json', [
        'plugin_id' => 'entity'
      ]));
    }
    return new JsonResponse($data);
  }

  /**
   * List users.
   */
  public function listUsers() {
    $users = $this->timesheetDefault->listUsers();
    $data = [];
    foreach ($users as $user) {
      $data[$user->id()] = $user->getDisplayName();
    }
    return new JsonResponse($data);
  }

  /**
   * List projects.
   */
  public function listProjects() {
    $nodes = $this->timesheetDefault->listProjects();
    $data = [];
    foreach ($nodes as $node) {
      $data[$node->id()] = $node->getTitle();
    }
    return new JsonResponse($data);
  }

  /**
   * List activities.
   */
  public function listActivities() {
    $terms = $this->timesheetDefault->listActivities();
    $data = [];
    foreach ($terms as $term) {
      $data[$term->tid] = $term->name;
    }
    return new JsonResponse($data);
  }
}
