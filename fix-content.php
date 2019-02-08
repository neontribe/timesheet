<?php

$nids = \Drupal::entityQuery('node')
  ->condition('status', 1)
  ->condition('type', 'timesheet_entry')
  ->execute();
$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);

$activities = [ 279, 280, 281, 283, 284,285, 287, 288, 289 ];

foreach ($nodes as $node) {
    $node->set('field_project', 277);
    $node->set('field_activity_type', $activities[rand(0, count($activities))]);
    $node->set('field_time_spent', 'PT0H' . rand(15, 180) . 'M');
    $node->save();
}



