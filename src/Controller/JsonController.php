<?php
namespace Drupal\timesheet\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\timesheet\Service\DefaultService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
   * By id.
   */
  public function byid($id) {
    $node = Node::load($id);
    $data = [];
    $data[$id] = json_decode($this->serializer->serialize($node, 'json', [
      'plugin_id' => 'entity'
    ]));
    return new JsonResponse($data);
  }

  public function new(Request $request) {
    $node = Node::create([
      'type' => 'time_sheet_entry'
    ]);
    return $this->processNode($node, $request);
  }

  public function update(Request $request, $id) {
    $node = Node::load($id);
    return $this->processNode($node, $request);
  }

  function processNode($node, $request) {
    $title = trim($request->request->get('title'));
    $field_activity_type = trim($request->request->get('field_activity_type'));
    $field_date = trim($request->request->get('field_date'));
    $field_duration = trim($request->request->get('field_duration'));
    $field_issue_uuid = trim($request->request->get('field_issue_uuid'));
    $field_project = trim($request->request->get('field_project'));
    $field_user = trim($request->request->get('field_user'));

    $node->setTitle($title);

    $node->set('field_activity_type', $field_activity_type);
    $node->set('field_date', $field_date);
    $node->set('field_duration', $field_duration);
    $node->set('field_project', $field_project);
    $node->set('field_user', $field_user);
    $node->set('field_duration', $field_duration);
    $node->set('field_issue_uuid', $field_issue_uuid);

    $sucess = $node->save();
    $data = json_decode($this->serializer->serialize($node, 'json', [
      'plugin_id' => 'entity'
    ]));

    return new JsonResponse($data, ($sucess ? 200 : 500));
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
