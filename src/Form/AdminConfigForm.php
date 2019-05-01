<?php
namespace Drupal\timesheet\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AdminConfigForm.
 */
class AdminConfigForm extends ConfigFormBase {

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
   * Constructs a new AdminConfigForm object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityManagerInterface $entity_manager, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($config_factory);
    $this->entityManager = $entity_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('config.factory'), $container->get('entity.manager'), $container->get('entity_type.manager'));
  }

  /**
   *
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'timesheet.adminconfig'
    ];
  }

  /**
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'timesheet_admin_config_form';
  }

  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('timesheet.adminconfig');

    $form['projects'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Projects'),
      '#rows' => 25,
      '#description' => $this->t('List the projects here with a pipe (|) seperator followed by the trello board id.  E.g. Some project|QswwEdRh'),
      '#default_value' => $config->get('projects')
    ];
    $form['import_entries'] = [
      '#type' => 'file',
      '#title' => $this->t('Import entries'),
      '#description' => $this->t('Import CSV file of timesheet entries'),
      '#default_value' => $config->get('import_entries')
    ];
    $form['clear_hashes'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Clear hashes'),
      '#description' => $this->t('Delete the hash table to allow re-importing of entires')
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('timesheet.adminconfig')
      ->set('projects', $form_state->getValue('projects'))
      ->set('import_entries', $form_state->getValue('import_entries'))
      ->save();

    $importfiles = $this->getRequest()->files->get('files', []);
    $importfile = $importfiles['import_entries'] ?? false;
    if ($importfile) {
      $lines = file($importfile->getRealPath());
      $batch = array(
        'title' => t('Importing timesheet data...'),
        'operations' => array(
          array(
            '\Drupal\timesheet\Batch\ImportFromCSV::batchOp',
            array(
              $lines
            )
          )
        ),
        'finished' => '\Drupal\timesheet\Batch\ImportFromCSV::importRowsFinishedCallback',
        'init_message' => t('Initialsing timesheet data import.'),
        'progress_message' => t('Processed @current out of @total.'),
        'error_message' => t('Timesheet import has encountered an error.')
      );
      batch_set($batch);
    }

    if ($form_state->getValue('clear_hashes')) {
      \Drupal::service('config.factory')->getEditable('timesheet.adminsettings')
        ->set('hashes', [])
        ->save();
      dsm("Hashes cleared");
    }
  }
}
