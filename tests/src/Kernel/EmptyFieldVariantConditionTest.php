<?php

namespace Drupal\Tests\typed_entity\Kernel;

use Drupal\node\Entity\Node;
use Drupal\typed_entity\TypedEntityContext;
use Drupal\typed_entity\WrappedEntityVariants\EmptyFieldVariantCondition;

/**
 * Test the FieldValueVariantCondition class.
 *
 * @coversDefaultClass \Drupal\typed_entity\WrappedEntityVariants\EmptyFieldVariantCondition
 *
 * @group typed_entity
 */
class EmptyFieldVariantConditionTest extends KernelTestBase {

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

    $condition = new EmptyFieldVariantCondition('field_node_type', new TypedEntityContext(['entity' => $article]));

    static::assertTrue($condition->evaluate());

    $article->field_node_type->value = 'News';
    $article->save();
    static::assertFalse($condition->evaluate());
  }

}
