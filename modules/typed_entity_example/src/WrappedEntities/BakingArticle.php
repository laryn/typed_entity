<?php

namespace Drupal\typed_entity_example\WrappedEntities;

/**
 * The wrapped entity for the article content type tagged with Baking.
 */
final class BakingArticle extends Article {

  /**
   * An example method that is specific for articles about baking.
   *
   * This is not useful at all, but used only as an example.
   *
   * @return string
   *   Either yeast or baking soda.
   */
  public function yeastOrBakingSoda(): string {
    return mt_rand(0, 1) ? 'yeast' : 'baking soda';
  }

}
