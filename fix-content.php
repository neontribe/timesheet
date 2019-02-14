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

    $duration = rand(15, 360);
    $hours = floor($duration / 60);
    $minutes = $duration - ($hours * 60);

    $node->set('field_time_spent', 'PT' . $hours . 'H' . $minutes . 'M');
    $node->set('field_duration_minutes_', $duration);
        
    $node->save();
}

// vim: set filetype=php expandtab tabstop=2 shiftwidth=2 autoindent smartindent:

