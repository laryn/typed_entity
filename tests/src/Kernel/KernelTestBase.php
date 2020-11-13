<?php

namespace Drupal\Tests\typed_entity\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase as BaseTestsKernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Base class with common functionality for typed_entity tests.
 */
abstract class KernelTestBase extends BaseTestsKernelTestBase {

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
    $this->installSchema('node', ['node_access']);

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

    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_node_type',
      'entity_type' => 'node',
      'type' => 'string',
    ]);
    $field_storage->save();

    $instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'article',
      'label' => $this->randomMachineName(),
    ]);
    $instance->save();

    $instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'page',
      'label' => $this->randomMachineName(),
    ]);
    $instance->save();
  }

  /**
   * Create and get array of articles.
   *
   * @return array
   *   An array of article nodes.
   */
  protected function createArticles() {
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

    return [
      $node,
      $node2,
      $node3,
    ];
  }

}
