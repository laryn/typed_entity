<?php

namespace Drupal\typed_entity_test\WrappedEntities;

/**
 * The wrapped entity for the article content type.
 */
class NewsArticle extends Article {

  /**
   * Get the byline for the article.
   */
  public function getByline() {
    return 'By John Doe';
  }

}
