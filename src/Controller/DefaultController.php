<?php

namespace Drupal\timesheet\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\timesheet\Service\ImportService;

/**
 * Class DefaultController.
 */
class DefaultController extends ControllerBase {

  /**
   * Drupal\timesheet\Service\ImportService definition.
   *
   * @var \Drupal\timesheet\Service\ImportService
   */
  protected $timesheetImport;

  /**
   * Constructs a new DefaultController object.
   */
  public function __construct(ImportService $timesheet_import) {
    $this->timesheetImport = $timesheet_import;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('timesheet.import')
    );
  }

  /**
   * Index.
   *
   * @return string
   *   Return Hello string.
   */
  public function index() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: index')
    ];
  }

}
