<?php

namespace Drupal\Tests\typed_entity\Kernel;

use Drupal\node\Entity\Node as DrupalNode;
use Drupal\typed_entity_test\WrappedEntities\Article;
use Drupal\typed_entity_test\WrappedEntities\NewsArticle;
use Drupal\typed_entity_test\WrappedEntities\Node;
use Drupal\typed_entity_test\WrappedEntities\User;
use Drupal\user\Entity\User as DrupalUser;

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
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testWrap(): void {
    $article = DrupalNode::create([
      'type' => 'article',
      'title' => $this->randomMachineName(),
    ]);
    $article->save();

    $page = DrupalNode::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
    ]);
    $page->save();

    $foo = DrupalNode::create([
      'type' => 'foo',
      'title' => $this->randomMachineName(),
    ]);
    $foo->save();

    $repository = typed_entity_repository_manager()->get('node.article');
    $article_wrapper = $repository->wrap($article);
    static::assertInstanceOf(Article::class, $article_wrapper);

    $article->field_node_type->value = 'News';
    $article->save();
    $article_wrapper = $repository->wrap($article);
    static::assertInstanceOf(NewsArticle::class, $article_wrapper);

    // The article repository cannot wrap pages or foo.
    static::assertNull($repository->wrap($page));
    static::assertNull($repository->wrap($foo));

    // Also test an entity type without bundles.
    $repository = typed_entity_repository_manager()->get('user');
    $user = $repository->wrap(DrupalUser::create(['name' => 'user']));
    static::assertInstanceOf(User::class, $user);

    // Test that nodes of type foo are wrapped by the generic Node.
    $repository = typed_entity_repository_manager()->get('node');
    static::assertInstanceOf(Node::class, $repository->wrap($article));
    static::assertInstanceOf(Node::class, $repository->wrap($page));
    static::assertInstanceOf(Node::class, $repository->wrap($foo));
  }

  /**
   * Test the wrapMultiple method.
   *
   * @covers ::wrapMultiple
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testWrapMultiple(): void {
    $repository = typed_entity_repository_manager()->get('node.article');

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
  public function testId(): void {
    $repository = typed_entity_repository_manager()->get('node.article');
    static::assertSame('node.article', $repository->id());
  }

  /**
   * Test the getQuery() method.
   *
   * @covers ::getQuery
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function testGetQuery(): void {
    $repository = typed_entity_repository_manager()->get('node.article');
    $query = $repository->getQuery();

    $this->createArticles();
    $page = DrupalNode::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
    ]);
    $page->save();

    static::assertSame('node', $query->getEntityTypeId());
    static::assertEquals(3, $query->accessCheck(TRUE)->count()->execute());
  }

}
