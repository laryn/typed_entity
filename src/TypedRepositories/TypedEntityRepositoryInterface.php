<?php


namespace Drupal\typed_entity\TypedRepositories;


use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\typed_entity\EntityWrappers\EntityWrapperInterface;

interface TypedEntityRepositoryInterface {

  public function createFromEntity(EntityInterface $entity): EntityWrapperInterface;
  public function init(EntityTypeInterface $entity_type, string $bundle);

}
