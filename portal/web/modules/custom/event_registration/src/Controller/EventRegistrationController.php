<?php

declare(strict_types=1);

namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns responses for Event registration routes.
 */
final class EventRegistrationController extends ControllerBase {

  /**
   * This register current user to specific event and save it in ECK register_event.
   */
  public function register_user($event): RedirectResponse|Response {
    $user = \Drupal::currentUser()->id();
    $user_entity = User::load($user);
    $event_node = \Drupal\node\Entity\Node::load($event);
    if (!$user_entity || !$event_node || $event_node->bundle() !== 'event') {
      return new Response('Invalid user or event.', 400);
    }
    $values = [
      'type' => 'registration', 
      'field_registered_user' => $user_entity->id(),
      'field_event' => $event_node->id(),
    ];
    $register = \Drupal::entityTypeManager()
      ->getStorage('register_event')
      ->create($values);
    $register->save();
    $this->messenger()->addStatus('User registered to event.');
    return new RedirectResponse('/event/' . $event);
  }

}
