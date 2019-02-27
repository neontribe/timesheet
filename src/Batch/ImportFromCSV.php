<?php

namespace Drupal\timesheet\Batch;

/**
 * Class ImportFromCSV.
 */
class ImportFromCSV {

  public function batchOp($fileUri, $blocksize, &$context) {
    if (empty($context['sandbox'])) {
      $context['sandbox']['current_row'] = 0;
      $context['sandbox']['max'] = count(file($fileUri));
    }

    // Do 20 records at a time.  TODO move this to config.
    $rows = file($fileUri);
    $max = $context['sandbox']['max'];
    $start = $context['sandbox']['current_row'];
    $end = min($start + $blocksize, $context['sandbox']['max']);

    $importService = \Drupal::service('timesheet.import');

    for ($i = $start; $i < $end; $i++) {
      $message = $importService->parseRow($rows[$i]);
      $context['results'][] = $message;
      $context['sandbox']['current_row'] ++;
    }

    $context['sandbox']['message'] = sprintf('Processed rows %d to %d', $start, $end);

    if ($context['sandbox']['current_row'] <= $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['current_row'] / $context['sandbox']['max'];
    }
  }

  public function importRowsFinishedCallback($success, $results, $operations) {
    if ($success) {
      $message = \Drupal::translation()
        ->formatPlural(count($results), 'One entry processed.', '@count entries processed.');
    }
    else {
      $message = t('Finished with an error.');
    }
    drupal_set_message($message);

    foreach ($results as $result) {
      if ($result) {
        drupal_set_message($result, 'warning');
      }
    }
  }

}
