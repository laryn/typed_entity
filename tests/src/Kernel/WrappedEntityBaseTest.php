<?php

namespace Drupal\Tests\typed_entity\Kernel;

use Drupal\node\Entity\Node;
use Drupal\Tests\field\Traits\EntityReferenceTestTrait;
use Drupal\typed_entity_test\WrappedEntities\Page;

/**
 * Test the FieldValueVariantCondition class.
 *
 * @coversDefaultClass \Drupal\typed_entity\WrappedEntities\WrappedEntityBase
 *
 * @group typed_entity
 */
class WrappedEntityBaseTest extends KernelTestBase {

  use EntityReferenceTestTrait;

  /**
   * A test node.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  private $node;

  /**
   * A test entity wrapper.
   *
   * @var \Drupal\typed_entity_test\WrappedEntities\Article
   */
  private $wrapper;

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUp(): void {
    parent::setUp();

    $this->createEntityReferenceField('node', 'article', 'field_related_pages', 'Related Pages', 'node');

    $this->node = Node::create([
      'type' => 'article',
      'title' => 'Test Article',
    ]);
    $this->node->save();

    $this->wrapper = typed_entity_repository_manager()->wrap($this->node);
  }

  /**
   * Test the getEntity method.
   *
   * @covers ::getEntity
   */
  public function testGetEntity(): void {
    $entity = $this->wrapper->getEntity();
    static::assertEquals($entity->id(), $this->node->id());
  }

  /**
   * Test the label method.
   *
   * @covers ::label
   */
  public function testLabel(): void {
    static::assertSame($this->wrapper->label(), $this->node->label());
  }

  /**
   * Test the wrapReferences method.
   *
   * @covers ::wrapReferences
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testWrapReferences(): void {
    $page = Node::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
    ]);
    $page->save();

    $page2 = Node::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
    ]);
    $page2->save();

    $page3 = Node::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
    ]);
    $page3->save();

    foreach ([$page, $page2, $page3] as $entity) {
      $this->node->field_related_pages[] = $entity->id();
    }
    $this->node->save();

    $wrapped_references = $this->wrapper->wrapReferences('field_related_pages');

    foreach ($wrapped_references as $reference) {
      static::assertInstanceOf(Page::class, $reference);
    }
  }

  /**
   * Test the wrapReference method.
   *
   * @covers ::wrapReference
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testWrapReference(): void {
    static::assertNull($this->wrapper->wrapReference('field_related_pages'));

    $page = Node::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
    ]);
    $page->save();

    $this->node->field_related_pages[] = $page->id();
    $this->node->save();

    $reference = $this->wrapper->wrapReference('field_related_pages');
    static::assertInstanceOf(Page::class, $reference);
  }

}
