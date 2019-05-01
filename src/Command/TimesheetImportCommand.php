<?php
namespace Drupal\timesheet\Command;

use Drupal\Console\Core\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TimesheetImportCommand.
 *
 * @\Drupal\Console\Annotations\DrupalCommand (
 *     extension="timesheet",
 *     extensionType="module"
 * )
 */
class TimesheetImportCommand extends ContainerAwareCommand {

  /**
   *
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('timesheet:import')
      ->setDescription('Read existing timesheet data from CSV')
      ->addArgument('filepath', InputArgument::REQUIRED, 'Path to the CSV file');
  }

  /**
   *
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $service = \Drupal::service('timesheet.import');
    $filepath = getCwd() . '/' . $input->getArgument('filepath');
    $lines = file($filepath);

    foreach ($lines as $line) {
      $message = $service->parseRow($line);
      if (! empty($message)) {
        $this->getIo()->info($message);
      }
    }

    $this->getIo()->info("Done");
  }
}
