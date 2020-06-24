<?php

namespace Drupal\typed_entity\Render;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\typed_entity\RendererCollector;
use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryBase;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;

final class TypedEntityBuilder {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The collector for renderer services.
   *
   * @var \Drupal\typed_entity\RendererCollector
   */
  private $rendererCollector;

  /**
   * TypedEntityBuilder constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\typed_entity\RendererCollector $collector
   *   The renderer collector.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererCollector $collector) {
    $this->entityTypeManager = $entity_type_manager;
    $this->rendererCollector = $collector;
  }

  public function build(WrappedEntityInterface $wrapped_entity, TypedEntityRenderContext $context): array {
    $builder = $this->wrappedEntityFactory($wrapped_entity, $context);
    return $builder->build($wrapped_entity, $context);
  }

  private function wrappedEntityFactory(WrappedEntityInterface $wrapped_entity, TypedEntityRenderContext $context): TypedEntityRendererInterface {
    $entity = $wrapped_entity->getEntity();
    $identifier = implode(
      TypedEntityRepositoryBase::SEPARATOR,
      array_filter([$context->getMachineName(), $entity->getEntityTypeId(), $entity->bundle()])
    );
    return $this->rendererCollector->get($identifier);
  }

}