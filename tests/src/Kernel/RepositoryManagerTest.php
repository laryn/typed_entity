<?php

namespace Drupal\Tests\typed_entity\Kernel;

use Drupal\node\Entity\Node;
use Drupal\typed_entity\RepositoryManager;
use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryBase;
use Drupal\typed_entity_test\TypedRepositories\ArticleRepository;
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
  public function testRepository() {
    $manager = \Drupal::service(RepositoryManager::class);
    assert($manager instanceof RepositoryManager);

    $repository = $manager->repository('node', 'article');
    $this->assert($repository instanceof ArticleRepository);

    $repository = $manager->repository('node', 'page');
    $this->assert($repository instanceof TypedEntityRepositoryBase);

    static::assertNull($manager->repository('foo', 'bar'));
  }

  /**
   * Test the repositoryFromEntity method.
   *
   * @covers ::repositoryFromEntity
   */
  public function testRepositoryFromEntity() {
    $node = Node::create([
      'type' => 'article',
      'title' => $this->randomMachineName(),
    ]);
    $node->save();

    $manager = \Drupal::service(RepositoryManager::class);
    assert($manager instanceof RepositoryManager);

    $repository = $manager->repositoryFromEntity($node);
    $this->assert($repository instanceof ArticleRepository);
  }

  /**
   * Test the wrap method.
   *
   * @covers ::wrap
   */
  public function testWrap() {
    $node = Node::create([
      'type' => 'article',
      'title' => $this->randomMachineName(),
    ]);
    $node->save();

    $manager = \Drupal::service(RepositoryManager::class);
    assert($manager instanceof RepositoryManager);

    $article_wrapper = $manager->wrap($node);
    $this->assert($article_wrapper instanceof Article);
  }

  /**
   * Test the wrapMultiple method.
   *
   * @covers ::wrapMultiple
   */
  public function testWrapMultiple() {
    $manager = \Drupal::service(RepositoryManager::class);
    assert($manager instanceof RepositoryManager);

    $article_wrappers = $manager->wrapMultiple($this->createArticles());
    foreach ($article_wrappers as $article_wrapper) {
      $this->assert($article_wrapper instanceof Article);
    }
  }

}
