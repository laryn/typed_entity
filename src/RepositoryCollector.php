<?php

namespace Drupal\typed_entity;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface;

final class RepositoryCollector {

  /**
   * The separator between the entity type ID and the bundle name.
   *
   * @var string
   */
  const SEPARATOR = ':';

  /**
   * The collected repositories.
   *
   * @var \Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface[]
   */
  private $repositories = [];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * RepositoryCollector constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * @param \Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface $repository
   *   The typed entity repository to collect.
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $bundle
   *   The bundle name.
   * @param string $variant
   *   An additional variant identifier.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function addRepository(
    TypedEntityRepositoryInterface $repository,
    $entity_type_id,
    $bundle,
    $variant = NULL
  ) {
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    $repository->init($entity_type, $bundle);
    $identifier = implode(
      static::SEPARATOR,
      array_filter([$entity_type_id, $bundle, $variant])
    );
    $this->repositories[$identifier] = $repository;
  }

}
