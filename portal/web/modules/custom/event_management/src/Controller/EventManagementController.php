<?php

declare(strict_types=1);

namespace Drupal\event_management\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns responses for event_management routes.
 */
final class EventManagementController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function list() {
    // Should be replaced with access checker.
    if (\Drupal::currentUser()->isAnonymous()) {
      throw new AccessDeniedHttpException('Access denied.');
    }
    $timestamp = time();
    $datetime = (new \DateTime())->setTimestamp($timestamp);
    $formatted = $datetime->format('Y-m-d\TH:i:s'); 
    $query = \Drupal::entityQuery("node")
    ->condition('type', 'event')
    ->condition('status', 1)
    ->condition('field_start_date', $formatted, '>')
    ->sort('field_start_date', 'ASC')
    ->range(0, 5)
    ->accessCheck(FALSE);
    $nids = $query->execute();

    $nodes = Node::loadMultiple($nids);
    $events = [];
    foreach ($nodes as $node) {
      $events[] = [
        'id' => $node->id(),
        'title' => $node->label(),
        'description' => $node->get("field_description")->value,
        'start_date' => $node->get('field_start_date')->date->getTimestamp(),
        'end_date' => $node->get('field_end_date')->date->getTimestamp(),
        'location' => $node->get("field_location")->value,
        'category_of_event' => $node->get("field_category_of_event")->entity->label(),
        'organizer_id' => $node->get("field_organizer")->target_id,
        'recurrence' => $node->get("field_recurrence")?->entity?->label()??"none",
      ];
    }
    return new JsonResponse($events);
  }

}
