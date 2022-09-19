<?php

namespace Drupal\Tests\typed_entity_example\Unit\Render\Article;

use Prophecy\PhpUnit\ProphecyTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;
use Drupal\typed_entity_example\Render\Article\Full;

/**
 * Tests the Full renderer.
 *
 * @coversDefaultClass \Drupal\typed_entity_example\Render\Article\Full
 *
 * @group typed_entity_example
 */
class FullTest extends UnitTestCase {

  use ProphecyTrait;
  /**
   * Tests the preprocessing for articles with the full view mode.
   *
   * @covers ::preprocess
   */
  public function testPreprocess(): void {
    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $fake_wrapped_entity = $this->prophesize(WrappedEntityInterface::class);
    $renderer = new Full($entity_type_manager->reveal());
    $build = [];
    $renderer->preprocess($build, $fake_wrapped_entity->reveal());
    $this->assertSame('background-color: cyan;', $build['attributes']['style'] ?? NULL);
  }

}
