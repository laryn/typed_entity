<?php

namespace Drupal\Tests\typed_entity\Unit;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Tests\UnitTestCase;
use Drupal\typed_entity\RepositoryManager;
use Drupal\typed_entity\TypedRepositories\TypedRepositoryBase;
use Drupal\typed_entity\TypedRepositories\TypedRepositoryInterface;
use Drupal\typed_entity\TypedRepositoryPluginManager;
use Prophecy\Argument;

/**
 * Tests RepositoryManager.
 *
 * @coversDefaultClass \Drupal\typed_entity\RepositoryManager
 *
 * @group typed_entity
 */
class RepositoryManagerTest extends UnitTestCase {

  /**
   * @covers ::get
   * @dataProvider getDataProvider
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function testGet($entity_type_id, $bundle, $times): void {
    $plugin_manager = $this->prophesize(TypedRepositoryPluginManager::class);
    $plugin_ids = [
      'foo.bar',
      'lorem.ipsum',
      'oof',
      'base:foo.baz',
      'base:rab',
    ];
    $plugin_manager->getDefinitions()->willReturn(array_combine($plugin_ids, $plugin_ids));
    $a_plugin = $this->prophesize(TypedRepositoryInterface::class)->reveal();
    $plugin_manager->createInstance(Argument::type('string'), Argument::type('array'))
      ->shouldBeCalledTimes($times)
      ->will(function ($args) use ($plugin_ids, $a_plugin) {
        [$id] = $args;
        if (in_array($id, $plugin_ids, TRUE)) {
          return $a_plugin;
        }
        throw new PluginNotFoundException('typed_entity_repository');
      });
    $repo_manager = new RepositoryManager(
      $this->prophesize(EntityTypeManager::class)->reveal(),
      $plugin_manager->reveal()
    );
    $repository_id = TypedRepositoryBase::generatePluginId($entity_type_id, $bundle);
    $instance = $repo_manager->get($repository_id);
    static::assertNotNull($instance);
  }

  /**
   * @covers ::get
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function testGetNull(): void {
    $plugin_manager = $this->prophesize(TypedRepositoryPluginManager::class);
    $plugin_manager->getDefinitions()->willReturn(['lol:iirc.wrt' => '']);
    $plugin_manager->createInstance(Argument::type('string'), Argument::type('array'))
      ->shouldBeCalledTimes(4)
      ->will(function () {
        throw new PluginNotFoundException('typed_entity_repository');
      });
    $repo_manager = new RepositoryManager(
      $this->prophesize(EntityTypeManager::class)->reveal(),
      $plugin_manager->reveal()
    );
    $repository_id = TypedRepositoryBase::generatePluginId('meh', 'phew');
    $instance = $repo_manager->get("something:$repository_id");
    static::assertNull($instance);
  }

  /**
   * Data provider for testGet.
   */
  public function getDataProvider(): array {
    return [
      ['foo', 'bar', 1],
      ['oof', 'faa', 3],
      ['oof', '', 1],
      ['foo', 'baz', 2],
      ['rab', 'red', 4],
    ];
  }

}
