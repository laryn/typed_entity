<?php

namespace Drupal\Tests\typed_entity\Kernel;

use Drupal\node\Entity\Node;
use Drupal\typed_entity\RepositoryManager;
use Drupal\typed_entity_test\WrappedEntities\Article;
use Drupal\typed_entity_test\WrappedEntities\NewsArticle;

/**
 * Test the TypedEntityRepositoryBase class.
 *
 * @coversDefaultClass \Drupal\typed_entity\TypedRepositories\TypedRepositoryBase
 *
 * @group typed_entity
 */
class TypedEntityRepositoryTest extends KernelTestBase {

  /**
   * Test the wrap method.
   *
   * @covers ::wrap
   */
  public function testWrap() {
    $article = Node::create([
      'type' => 'article',
      'title' => $this->randomMachineName(),
    ]);
    $article->save();

    $page = Node::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
    ]);
    $page->save();

    $repository = $this->getArticleRepository();
    $article_wrapper = $repository->wrap($article);
    static::assertInstanceOf(Article::class, $article_wrapper);

    $article->field_node_type->value = 'News';
    $article->save();
    $article_wrapper = $repository->wrap($article);
    static::assertInstanceOf(NewsArticle::class, $article_wrapper);

    static::assertNull($repository->wrap($page));
  }

  /**
   * Get the ArticleRepository from the RepositoryManager.
   */
  private function getArticleRepository() {
    $collector = typed_entity_repository_manager();
    assert($collector instanceof RepositoryManager);

    return $collector->get('node:article');
  }

  /**
   * Test the wrapMultiple method.
   *
   * @covers ::wrapMultiple
   */
  public function testWrapMultiple() {
    $repository = $this->getArticleRepository();

    $article_wrappers = $repository->wrapMultiple($this->createArticles());
    foreach ($article_wrappers as $article_wrapper) {
      static::assertInstanceOf(Article::class, $article_wrapper);
    }
  }

  /**
   * Test the id() method.
   *
   * @covers ::id
   */
  public function testId() {
    $repository = $this->getArticleRepository();
    static::assertSame('node:article', $repository->id());
  }

  /**
   * Test the getQuery() method.
   *
   * @covers ::getQuery
   */
  public function testGetQuery() {
    $repository = $this->getArticleRepository();
    $query = $repository->getQuery();

    $this->createArticles();
    $page = Node::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
    ]);
    $page->save();

    static::assertSame('node', $query->getEntityTypeId());
    static::assertEquals(3, $query->count()->execute());
  }

}
