<?php

namespace Drupal\Tests\typed_entity_example\Unit\WrappedEntities;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\typed_entity_example\WrappedEntities\User;

/**
 * Tests the Article wrapped entity.
 *
 * @coversDefaultClass \Drupal\typed_entity_example\WrappedEntities\User
 *
 * @group typed_entity_example
 */
class UserTest extends UnitTestCase {

  /**
   * Tests the nickname.
   *
   * @covers ::nickname
   */
  public function testNickname() {
    $user_entity = $this->prophesize(EntityInterface::class);
    $user_entity->mail = (object) ['value' => 'foo@lorem.ipsum'];
    $sut = new User($user_entity->reveal());
    $this->assertSame('foo', $sut->nickname());
  }

}
