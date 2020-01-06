<?php

namespace Drupal\typed_entity_example\WrappedEntities;

use Drupal\Core\Entity\EntityInterface;
use Drupal\typed_entity\WrappedEntities\WrappedEntityBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The wrapped entity for the article content type.
 */
class Article extends WrappedEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, EntityInterface $entity) {
    return new static($entity);
  }

}
