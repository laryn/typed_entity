<?php

namespace Drupal\Tests\typed_entity\Kernel;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\node\Entity\Node;
use Drupal\typed_entity\InvalidValueException;
use Drupal\typed_entity\RepositoryCollector;
use Drupal\typed_entity_test\WrappedEntities\Article;
use Drupal\typed_entity_test\WrappedEntities\NewsArticle;
use Drupal\typed_entity_test\WrappedEntities\Page;
use UnexpectedValueException;

/**
 * Test the TypedEntityRepositoryBase class.
 *
 * @coversDefaultClass \Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryBase
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
    $this->assertInstanceOf(Article::class, $article_wrapper);

    $article->field_type->value = 'News';
    $article->save();
    $article_wrapper = $repository->wrap($article);
    $this->assertInstanceOf(NewsArticle::class, $article_wrapper);

    $this->expectException(InvalidValueException::class);
    $repository->wrap($page);
  }

  /**
   * Get the ArticleRepository from the RepositoryCollector.
   */
  private function getArticleRepository() {
    $collector = \Drupal::service(RepositoryCollector::class);
    assert($collector instanceof RepositoryCollector);

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
      $this->assertInstanceOf(Article::class, $article_wrapper);
    }
  }

  /**
   * Test the id() method.
   *
   * @covers ::id
   */
  public function testId() {
    $repository = $this->getArticleRepository();
    $this->assertSame('node:article', $repository->id());
  }

  /**
   * Test the init() method.
   *
   * @covers ::init
   */
  public function testInit() {
    $repository = $this->getArticleRepository();

    $entity_type = \Drupal::entityTypeManager()->getDefinition('node');
    $repository->init($entity_type, 'page', Page::class);

    $page = Node::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
    ]);
    $page->save();

    $page_wrapper = $repository->wrap($page);
    $this->assertInstanceOf(Page::class, $page_wrapper);

    $this->expectException(UnexpectedValueException::class);
    $repository->init($entity_type, '', Page::class);
  }

  /**
   * Test the getQuery() method.
   *
   * @covers ::getQuery
   */
  public function testGetQuery() {
    $repository = $this->getArticleRepository();
    $query = $repository->getQuery();
    $this->assertInstanceOf(QueryInterface::class, $query);

    $this->createArticles();
    $page = Node::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
    ]);
    $page->save();

    $this->assertSame('node', $query->getEntityTypeId());
    $this->assertEquals(3, $query->count()->execute());
  }

}
