<?php
namespace Drupal\timesheet\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\timesheet\Service\DefaultService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WebController.
 */
class WebController extends ControllerBase {

  /**
   * Drupal\timesheet\Service\DefaultService definition.
   *
   * @var \Drupal\timesheet\Service\DefaultService
   */
  protected $timesheetDefault;

  /**
   * Constructs a new WebController object.
   */
  public function __construct(DefaultService $timesheet_default) {
    $this->timesheetDefault = $timesheet_default;
  }

  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('timesheet.default'));
  }

  /**
   * List.
   *
   * https://tobias.batch.org.uk/timeshite/web/timesheet/list/UMM8sQwK/9DpXF2bz
   *
   * @return string Return Hello string.
   */
  public function list($board, $uuid) {
    $nodes = $this->timesheetDefault->byuuid($uuid);

    return [
      'timesheets' => [
        '#theme' => 'timesheet_byuuid',
        '#entries' => $nodes,
        '#uuid' => $uuid,
        '#board' => $board
      ]
    ];
  }

  /**
   * Edit.
   *
   * @return string Return Hello string.
   */
  public function edit($board, $uuid, $nid) {
    $node = Node::load($nid);

    if (! $node) {
      return [
        'links' => [
          '#theme' => 'timesheet_links',
          '#uuid' => $uuid,
          '#board' => $board
        ],
        'message' => [
          '#theme' => 'timesheet_404',
          '#id' => $nid
        ]
      ];
    }

    $form = \Drupal::service('entity.form_builder')->getForm($node);
    $form['#attached']['library'][] = 'timesheet/timesheet';

    return [
      'form' => $form,
      'links' => [
        '#theme' => 'timesheet_links',
        '#uuid' => $uuid,
        '#board' => $board
      ]
    ];
  }

  /**
   * New.
   *
   * @return string Return Hello string.
   */
  public function new($board, $uuid) {
    $node = \Drupal::entityTypeManager()->getStorage('node')->create([
      'type' => 'time_sheet_entry'
    ]);
    $project = $this->timesheetDefault->getProject($board);
    $node->set('field_issue_uuid', $uuid);
    $node->set('field_project', $project);

    $form = \Drupal::service('entity.form_builder')->getForm($node);
    $form['#attached']['library'][] = 'timesheet/timesheet';

    return [
      'form' => $form,
      'links' => [
        '#theme' => 'timesheet_links',
        '#uuid' => $uuid,
        '#board' => $board
      ]
    ];
  }
}
