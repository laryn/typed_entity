<?php

namespace Drupal\Tests\typed_entity\Kernel;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\typed_entity\InvalidValueException;
use Drupal\typed_entity\WrappedEntityVariants\FieldValueVariantCondition;
use Drupal\typed_entity_test\WrappedEntities\NewsArticle;

/**
 * Test the FieldValueVariantCondition class.
 *
 * @coversDefaultClass \Drupal\typed_entity\WrappedEntityVariants\FieldValueVariantCondition
 *
 * @group typed_entity
 */
class FieldValueVariantConditionTest extends KernelTestBase {

  /**
   * Test the isNegated method.
   *
   * @covers ::isNegated
   */
  public function testIsNegated() {
    $condition = new FieldValueVariantCondition('field_node_type', 'News', NewsArticle::class);
    static::assertFalse($condition->isNegated());

    $condition = new FieldValueVariantCondition('field_node_type', 'News', NewsArticle::class, TRUE);
    static::assertTrue($condition->isNegated());
  }

  /**
   * Test the evaluate method.
   *
   * @covers ::evaluate
   */
  public function testEvalutate() {
    $article = Node::create([
      'type' => 'article',
      'title' => $this->randomMachineName(),
    ]);
    $article->save();

    $condition = new FieldValueVariantCondition('field_node_type', 'News', NewsArticle::class);

    $condition->setContext('entity', $article);
    static::assertFalse($condition->evaluate());

    $article->field_node_type->value = 'News';
    $article->save();
    static::assertTrue($condition->evaluate());
  }

  /**
   * Test the summary method.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $condition = new FieldValueVariantCondition('field_node_type', 'News', NewsArticle::class);
    $summary = 'Active when the <em class="placeholder">field_node_type</em> is <em class="placeholder">News</em>.';
    static::assertSame($condition->summary()->__toString(), $summary);
  }

  /**
   * Test the variant method.
   *
   * @covers ::variant
   */
  public function testVariant() {
    $condition = new FieldValueVariantCondition('field_node_type', 'News', NewsArticle::class);
    static::assertSame($condition->variant(), NewsArticle::class);
  }

  /**
   * Test the exception throwing of validateContext method.
   *
   * @covers ::validateContext
   */
  public function validateContextNoEntity() {
    $this->createFooNodeType();

    $condition = new FieldValueVariantCondition('field_node_type', 'News', NewsArticle::class);
    $condition->setContext('entity', '');

    $this->expectException(InvalidValueException::class);
    $condition->validateContext();

  }

  /**
   * Test the exception throwing of validateContext method.
   *
   * @covers ::validateContext
   */
  public function validateContextNoField() {
    $this->createFooNodeType();
    $node = Node::create([
      'type' => 'foo',
      'title' => $this->randomMachineName(),
    ]);
    $node->save();

    $condition = new FieldValueVariantCondition('field_node_type', 'News', NewsArticle::class);
    $condition->setContext('entity', $node);

    $this->expectException(InvalidValueException::class);
    $condition->validateContext();
  }

  /**
   * Create a dummy node type.
   */
  private function createFooNodeType() {
    $node_type = NodeType::create([
      'type' => 'foo',
      'name' => 'Foo',
      'description' => "A very fooey node type.",
    ]);
    $node_type->save();
  }

}
