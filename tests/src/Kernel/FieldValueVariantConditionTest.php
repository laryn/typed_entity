<?php

namespace Drupal\Tests\typed_entity\Kernel;

use Drupal\node\Entity\Node;
use Drupal\typed_entity\InvalidValueException;
use Drupal\typed_entity\TypedEntityContext;
use Drupal\typed_entity\WrappedEntityVariants\FieldValueVariantCondition;

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
  public function testIsNegated(): void {
    $condition = new FieldValueVariantCondition('field_node_type', 'News', new TypedEntityContext());
    static::assertFalse($condition->isNegated());

    $condition = new FieldValueVariantCondition('field_node_type', 'News', new TypedEntityContext(), TRUE);
    static::assertTrue($condition->isNegated());
  }

  /**
   * Test the evaluate method.
   *
   * @covers ::evaluate
   *
   * @throws \Drupal\typed_entity\InvalidValueException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testEvalutate(): void {
    $article = Node::create([
      'type' => 'article',
      'title' => $this->randomMachineName(),
    ]);
    $article->save();

    $condition = new FieldValueVariantCondition('field_node_type', 'News', new TypedEntityContext(['entity' => $article]));
    $empty_condition = new FieldValueVariantCondition('field_node_type', NULL, new TypedEntityContext(['entity' => $article]));

    static::assertFalse($condition->evaluate());
    static::assertTrue($empty_condition->evaluate());

    $article->field_node_type->value = 'News';
    $article->save();
    static::assertTrue($condition->evaluate());
    static::assertFalse($empty_condition->evaluate());
  }

  /**
   * Test the summary method.
   *
   * @covers ::summary
   */
  public function testSummary(): void {
    $condition = new FieldValueVariantCondition('field_node_type', 'News', new TypedEntityContext());
    $summary = 'Active when the <em class="placeholder">field_node_type</em> is <em class="placeholder">News</em>.';
    static::assertSame($condition->summary()->__toString(), $summary);
  }

  /**
   * Test the exception throwing of validateContext method.
   *
   * @covers ::validateContext
   */
  public function testValidateContextNoEntity(): void {
    $condition = new FieldValueVariantCondition('field_node_type', 'News', new TypedEntityContext(['entity' => '']));

    $this->expectException(InvalidValueException::class);
    $condition->validateContext();
  }

  /**
   * Test the exception throwing of validateContext method.
   *
   * @covers ::validateContext
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testValidateContextNoField(): void {
    $node = Node::create([
      'type' => 'foo',
      'title' => $this->randomMachineName(),
    ]);
    $node->save();

    $condition = new FieldValueVariantCondition('field_node_type', 'News', new TypedEntityContext(['entity' => $node]));

    $this->expectException(InvalidValueException::class);
    $condition->validateContext();
  }

}
