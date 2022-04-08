<?php

namespace Drupal\typed_entity_ui\Form\RepositoryTable;

/**
 * Action service response class for building the repository table.
 */
class RepositoryTableResponse {

  /**
   * The render array.
   *
   * @var array
   */
  protected array $build = [];

  /**
   * RepositoryTableResponse constructor.
   *
   * @param array $build
   *   The render array.
   */
  public function __construct(array $build) {
    $this->build = $build;
  }

  /**
   * Gets the render array.
   *
   * @return array
   *   The array.
   */
  public function getBuild(): array {
    return $this->build;
  }

}
