<?php

namespace Drupal\Tests\typed_entity\Kernel;

use Drupal\node\Entity\Node;
use Drupal\Tests\field\Traits\EntityReferenceTestTrait;
use Drupal\typed_entity\RepositoryManager;
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
   */
  protected function setUp() {
    parent::setUp();

    $this->createEntityReferenceField('node', 'article', 'field_related_pages', 'Related Pages', 'node');

    $this->node = Node::create([
      'type' => 'article',
      'title' => 'Test Article',
    ]);
    $this->node->save();

    $this->wrapper = \Drupal::service(RepositoryManager::class)->wrap($this->node);
  }

  /**
   * Test the getEntity method.
   *
   * @covers ::getEntity
   */
  public function testGetEntity() {
    $entity = $this->wrapper->getEntity();
    static::assertEquals($entity->id(), $this->node->id());
  }

  /**
   * Test the label method.
   *
   * @covers ::label
   */
  public function testLabel() {
    static::assertSame($this->wrapper->label(), $this->node->label());
  }

  /**
   * Test the wrapReferences method.
   *
   * @covers ::wrapReferences
   */
  public function testWrapReferences() {
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
   */
  public function testWrapReference() {
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

  /**
   * Test the toRenderable method.
   *
   * @covers ::toRenderable
   */
  public function testToRenderable() {
    $node_view = $this->wrapper->toRenderable();
    static::assertSame('default', $node_view['#view_mode']);
    static::assertArrayHasKey('#node', $node_view);

    $this->wrapper->setViewMode('teaser');
    $node_view = $this->wrapper->toRenderable();
    static::assertSame('teaser', $node_view['#view_mode']);
  }

}
