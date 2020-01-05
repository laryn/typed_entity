<?php

namespace Drupal\typed_entity_example\EntityWrappers;

use Drupal\typed_entity\EntityWrappers\EntityWrapperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Article implements EntityWrapperInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

}