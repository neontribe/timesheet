<?php
/**
 * @file
 * Contains \Drupal\timesheet\Theme\ThemeNegotiator
 */
namespace Drupal\timesheet\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

class ThemeNegotiator implements ThemeNegotiatorInterface {

  /**
   *
   * {@inheritdoc}
   */
  function applies(RouteMatchInterface $route_match) {
    $route_name = $route_match->getRouteName();

    return (strpos($route_name, 'timesheet.web') === 0);
  }

  /**
   *
   * {@inheritdoc}
   */
  function determineActiveTheme(RouteMatchInterface $route_match) {
    return 'timesheet_iframe';
  }
}