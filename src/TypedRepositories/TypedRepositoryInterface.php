<?php

namespace Drupal\typed_entity\TypedRepositories;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\typed_entity\EntityWrapperInterface;
use Drupal\typed_entity\Render\TypedEntityRendererInterface;
use Drupal\typed_entity\TypedEntityContext;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;

/**
 * Entity repository.
 */
interface TypedRepositoryInterface extends EntityWrapperInterface {

  /**
   * The separator between the entity type ID and the bundle name.
   *
   * @var string
   */
  const SEPARATOR = ':';

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
   * Gets the wrapper for the current context.
   *
   * Override this method in your repository for more nuanced rules on when to
   * use a wrapper or another.
   *
   * @param \Drupal\typed_entity\TypedEntityContext $context
   *   The context used for render.
   *
   * @return \Drupal\typed_entity\WrappedEntities\WrappedEntityInterface|null
   *   The first renderer that applies.
   */
  public function wrapperFactory(TypedEntityContext $context): ?WrappedEntityInterface;

  /**
   * Gets the renderer for the current context.
   *
   * Override this method in your repository for more nuanced rules on when to
   * use a renderer or another.
   *
   * @param \Drupal\typed_entity\TypedEntityContext $context
   *   The context used for render.
   *
   * @return \Drupal\typed_entity\Render\TypedEntityRendererInterface|null
   *   The first renderer that applies.
   */
  public function rendererFactory(TypedEntityContext $context): ?TypedEntityRendererInterface;

}