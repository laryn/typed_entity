<?php

namespace Drupal\typed_entity;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface;

/**
 * Collects the repositories.
 */
final class RepositoryCollector {

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
   * Adds a repository to the list.
   *
   * @param \Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface $repository
   *   The typed entity repository to collect.
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $wrapper_class
   *   The FQN for the class that will wrap this entity.
   * @param string $bundle
   *   The bundle name.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @todo: The variant negotiation is still missing.
   */
  public function addRepository(
    TypedEntityRepositoryInterface $repository,
    string $entity_type_id,
    string $wrapper_class,
    string $bundle = ''
  ) {
    if (empty($entity_type_id)) {
      // We get an empty entity type ID when processing the parent service. We
      // do not want to include it in the collection.
      return;
    }
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    $repository->init($entity_type, $bundle, $wrapper_class);
    $this->repositories[$repository->id()] = $repository;
  }

  /**
   * Get a repository.
   *
   * @param string $repository_id
   *   The repository identifier.
   *
   * @return \Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface|null
   *   The repository.
   */
  public function get(string $repository_id): ?TypedEntityRepositoryInterface {
    return $this->repositories[$repository_id] ?? NULL;
  }

}
