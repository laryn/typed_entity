<?php

namespace Drupal\Tests\typed_entity\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\typed_entity\RepositoryManager;
use Drupal\typed_entity\RepositoryNotFoundException;
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
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'user',
    'node',
    'field',
    'text',
    'typed_entity',
    'typed_entity_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig(['node', 'field', 'system']);

    $node_type = NodeType::create([
      'type' => 'page',
      'name' => 'Basic page',
      'description' => "Use <em>basic pages</em> for your static content, such as an 'About us' page.",
    ]);
    $node_type->save();
    $node_type = NodeType::create([
      'type' => 'article',
      'name' => 'Article',
      'description' => "Use <em>articles</em> for time-sensitive content like news, press releases or blog posts.",
    ]);
    $node_type->save();
  }

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

    $this->expectException(RepositoryNotFoundException::class);
    $manager->repository('foo', 'bar');
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
    $node = Node::create([
      'type' => 'article',
      'title' => $this->randomMachineName(),
    ]);
    $node->save();

    $node2 = Node::create([
      'type' => 'article',
      'title' => $this->randomMachineName(),
    ]);
    $node2->save();

    $node3 = Node::create([
      'type' => 'article',
      'title' => $this->randomMachineName(),
    ]);
    $node3->save();

    $manager = \Drupal::service(RepositoryManager::class);
    assert($manager instanceof RepositoryManager);

    $article_wrappers = $manager->wrapMultiple([
      $node,
      $node2,
      $node3,
    ]);
    foreach ($article_wrappers as $article_wrapper) {
      $this->assert($article_wrapper instanceof Article);
    }
  }

}
