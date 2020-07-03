<?php

namespace Drupal\typed_entity\TypedRepositories;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\typed_entity\EntityWrapperInterface;
use Drupal\typed_entity\Render\TypedEntityRenderContext;
use Drupal\typed_entity\Render\TypedEntityRendererInterface;

/**
 * Entity repository.
 */
interface TypedEntityRepositoryInterface extends EntityWrapperInterface {

  /**
   * Initialize the repository with the parameters in the service container.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type for this repository.
   * @param string $bundle
   *   The bundle name.
   * @param string $wrapper_class
   *   The class to instantiate when creating wrapped entities using this
   *   repository.
   */
  public function init(EntityTypeInterface $entity_type, string $bundle, string $wrapper_class): void;

  /**
   * Gets a query to start finding items.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The query to execute.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getQuery(): QueryInterface;

  /**
   * Build the repository identifier.
   *
   * @return string
   *   The identifier.
   *
   * @SuppressWarnings(PHPMD.ShortMethodName)
   */
  public function id(): string;

  /**
   * Gets the renderer for the current context.
   *
   * Override this method in your repository for more nuanced rules on when to
   * use a renderer or another.
   *
   * @param \Drupal\typed_entity\Render\TypedEntityRenderContext $context
   *   The context used for render.
   *
   * @return \Drupal\typed_entity\Render\TypedEntityRendererInterface[]
   *   All the renderers that might apply.
   */
  public function rendererFactory(TypedEntityRenderContext $context): array;

  /**
   * Adds a renderer.
   *
   * @param \Drupal\typed_entity\Render\TypedEntityRendererInterface $renderer
   *   The renderer.
   */
  public function addRenderer(TypedEntityRendererInterface $renderer): void;

}
