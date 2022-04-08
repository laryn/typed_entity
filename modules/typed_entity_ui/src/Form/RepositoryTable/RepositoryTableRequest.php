<?php

namespace Drupal\typed_entity_ui\Form\RepositoryTable;

use Drupal\typed_entity\RepositoryManager;

/**
 * Action service request class for building the repository table.
 */
class RepositoryTableRequest {

  /**
   * The plugin manager.
   *
   * @var \Drupal\typed_entity\RepositoryManager
   */
  protected RepositoryManager $repositoryManager;

  /**
   * RepositoryTableRequest constructor.
   *
   * @param \Drupal\typed_entity\RepositoryManager $repository_manager
   *   The plugin manager.
   */
  public function __construct(RepositoryManager $repository_manager) {
    $this->repositoryManager = $repository_manager;
  }

  /**
   * The typed repositories.
   *
   * @return array
   *   The list of repositories.
   */
  public function getAllTypedRepositories(): array {
    return $this->repositoryManager->getAll();
  }

}
