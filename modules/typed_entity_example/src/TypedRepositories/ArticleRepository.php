<?php

namespace Drupal\typed_entity_example\TypedRepositories;

use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryBase;
use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface;
use Drupal\typed_entity_example\EntityWrappers\Article;

class ArticleRepository extends TypedEntityRepositoryBase {

  protected static $wrapperClass = Article::class;

}
