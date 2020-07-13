<?php

namespace Drupal\typed_entity_test\Render\Article;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\typed_entity\Render\TypedEntityRendererBase;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;

/**
 * Business logic to render the article as a teaser.
 */
final class Teaser extends TypedEntityRendererBase {

  /**
   * {@inheritdoc}
   */
  const VIEW_MODE = 'teaser';

  /**
   * {@inheritdoc}
   */
  public function preprocess(array &$variables, WrappedEntityInterface $wrapped_entity): void {
    parent::preprocess($variables, $wrapped_entity);
    $variables['attributes']['data-variables-are-preprocessed'] = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function viewAlter(array &$build, WrappedEntityInterface $wrapped_entity, EntityViewDisplayInterface $display): void {
    parent::viewAlter($build, $wrapped_entity, $display);
    $build['title'] = ['#markup' => '<h4>Altered title</h4>'];
  }

}
