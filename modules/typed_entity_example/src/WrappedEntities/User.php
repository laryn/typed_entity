<?php

namespace Drupal\typed_entity_example\WrappedEntities;

use Drupal\typed_entity\WrappedEntities\WrappedEntityBase;

/**
 * Wraps the user entity.
 */
final class User extends WrappedEntityBase {

  public function nickname() {
    // According to our stakeholders the nickname is the part before the @ in
    // the registration email.
    $email = $this->getEntity()->mail->value;
    $parts = explode('@', $email);
    return reset($parts);
  }

}
