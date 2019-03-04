<?php

namespace Drupal\timesheet\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ChromeInjectionController.
 */
class ChromeInjectionController extends ControllerBase {

  protected $twig;

  public function __construct(\Twig_Environment $twig) {
    $this->twig = $twig;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('twig')
    );
  }

  /**
   * Timespent.
   *
   * @return string
   *   Return Hello string.
   */
  public function summary($cardid) {
    // TODO: This should be injected by dependancy injection.
    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['field_remote_card' => $cardid]);

    $activities = [];
    $minutes = 0;
    foreach ($nodes as $node) {
      $timespent = $node->get('field_time_spent')->first()->getValue()['value'];
      $duration = new \DateInterval($timespent);
      // The duration field happily stores 90m rather than 1h30m to cast to seconds.
      $_minutes = $duration->h * 60 + $duration->i;
      $minutes += $_minutes;
      $activities[] = [
        'title' => $node->getTitle(),
        'timespent' => sprintf('%02d:%02d', floor($_minutes / 60), $_minutes % 60),
      ];
    }
    $vars = [
      'activities' => $activities,
      'total' => sprintf('%02d:%02d', floor($minutes / 60), $minutes % 60),
      'url' => \Drupal\Core\Url::fromRoute('<current>', array(), array("absolute" => TRUE))->toString(),
    ];

    $twigFilePath = drupal_get_path('module', 'timesheet') . '/templates/timesheet-summary.html.twig';
    $template = $this->twig->loadTemplate($twigFilePath);
    $markup = $template->render($vars);

    return new Response($markup);
  }

  /**
   * Logtime.
   *
   * @return string
   *   Return Hello string.
   */
  public function logtime() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: logtime')
    ];
  }

}
