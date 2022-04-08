<?php

namespace Drupal\Tests\typed_entity\Kernel;

use Drupal\node\Entity\Node;
use Drupal\typed_entity\TypedRepositories\TypedRepositoryBase;
use Drupal\typed_entity_test\Plugin\TypedRepositories\ArticleRepository;
use Drupal\typed_entity_test\WrappedEntities\Article;

/**
 * Test the RepositoryManager class.
 *
 * @coversDefaultClass \Drupal\typed_entity\RepositoryManager
 *
 * @group typed_entity
 */
class RepositoryManagerTest extends KernelTestBase {

  /**
   * Test the repository method.
   *
   * @covers ::repository
   */
  public function testRepository(): void {
    $manager = typed_entity_repository_manager();
    $repository = $manager->repository('node', 'article');
    $this->assertInstanceOf(ArticleRepository::class, $repository);

    $repository = $manager->repository('node', 'page');
    $this->assertInstanceOf(TypedRepositoryBase::class, $repository);

    static::assertNull($manager->repository('foo', 'bar'));
  }

  /**
   * Test the repositoryFromEntity method.
   *
   * @covers ::repositoryFromEntity
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testRepositoryFromEntity(): void {
    $node = Node::create([
      'type' => 'article',
      'title' => $this->randomMachineName(),
    ]);
    $node->save();

    $manager = typed_entity_repository_manager();
    $repository = $manager->repositoryFromEntity($node);
    $this->assertInstanceOf(ArticleRepository::class, $repository);
  }

  /**
   * Test the wrap method.
   *
   * @covers ::wrap
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testWrap(): void {
    $node = Node::create([
      'type' => 'article',
      'title' => $this->randomMachineName(),
    ]);
    $node->save();

    $manager = typed_entity_repository_manager();
    $article_wrapper = $manager->wrap($node);
    $this->assertInstanceOf(Article::class, $article_wrapper);
  }

  /**
   * Test the wrapMultiple method.
   *
   * @covers ::wrapMultiple
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testWrapMultiple(): void {
    $manager = typed_entity_repository_manager();
    $article_wrappers = $manager->wrapMultiple($this->createArticles());
    foreach ($article_wrappers as $article_wrapper) {
      $this->assertInstanceOf(Article::class, $article_wrapper);
    }
  }

}
