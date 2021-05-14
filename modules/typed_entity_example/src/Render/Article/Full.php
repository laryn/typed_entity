<?php

namespace Drupal\typed_entity_example\Render\Article;

use Drupal\typed_entity\Render\TypedEntityRendererBase;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;

/**
 * Renderer for articles when using the 'summary' view mode.
 */
class Full extends TypedEntityRendererBase {

  const VIEW_MODE = 'full';

  /**
   * {@inheritdoc}
   */
  public function preprocess(array &$variables, WrappedEntityInterface $wrapped_entity): void {
    // You can do whatever you need to in here. For *the sake of the example* I
    // will modify the style property directly.
    $variables['attributes']['style'] = 'background-color: cyan;';
  }

}
