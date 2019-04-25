<?php
namespace Drupal\timesheet\Service;

use Drupal\webprofiler\Entity\EntityManagerWrapper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Class DefaultService.
 */
class DefaultService {

  /**
   * Symfony\Component\DependencyInjection\ContainerAwareInterface definition.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerAwareInterface
   */
  protected $entityQuery;

  /**
   * Drupal\webprofiler\Entity\EntityManagerWrapper definition.
   *
   * @var \Drupal\webprofiler\Entity\EntityManagerWrapper
   */
  protected $entityTypeManager;

  /**
   * Constructs a new DefaultService object.
   */
  public function __construct(ContainerAwareInterface $entity_query, EntityManagerWrapper $entity_type_manager) {
    $this->entityQuery = $entity_query;
    $this->entityTypeManager = $entity_type_manager;
  }
}
