<?php

namespace Drupal\typed_entity_example\WrappedEntities;

/**
 * The wrapped entity for the article content type tagged with Baking.
 */
final class BakingArticle extends Article {

  public function yeastOrBakingSoda(): string {
    return mt_rand(0, 1) ? 'yeast' : 'baking soda';
  }

}
