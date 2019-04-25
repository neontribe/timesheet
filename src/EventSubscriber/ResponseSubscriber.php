<?php

namespace Drupal\timesheet\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ResponseSubscriber.
 */
class ResponseSubscriber implements EventSubscriberInterface {


  /**
   * Constructs a new ResponseSubscriber object.
   */
  public function __construct() {

  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRespond'];

    return $events;
  }

  /**
   * This method is called whenever the onRespond event is
   * dispatched.
   *
   * @param GetResponseEvent $event
   */
  public function onRespond(Event $event) {
    $request = $event->getRequest();
    $path = $request->getPathInfo();

    // TODO: make this a constant.
    $allowedPaths = [
      'timesheet/*',
      'session/token',
    ];
    error_log("HERE", ERR_WRAN);

    $matched = count(array_filter(
      $allowedPaths,
      function($entry) use ($path) {
        if (preg_match_all("|" . $entry . "|", $path)) {
          return true;
        }
      }
    )
    );

    if ($matched) {
      $event->getResponse()->headers->remove('X-Frame-Options');
    }
    // drupal_set_message('Event onRespond thrown by Subscriber in module timesheet.' . $path, 'status', TRUE);
  }

}
