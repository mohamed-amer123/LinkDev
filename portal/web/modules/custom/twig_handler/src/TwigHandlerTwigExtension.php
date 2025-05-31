<?php

declare(strict_types=1);

namespace Drupal\twig_handler;

use Drupal\user\Entity\User;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Twig extension.
 */
final class TwigHandlerTwigExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    $functions[] = new TwigFunction('get_event_sessions', [$this, 'getEventSessions']);
    $functions[] = new TwigFunction('check_event_register', [$this, 'checkEventRegister']);
    return $functions;
  }

  public function checkEventRegister($event_id) {
    $user = \Drupal::currentUser()->id();
    $user_entity = User::load($user);
    $event_node = \Drupal\node\Entity\Node::load($event_id);
    if (!$user_entity || !$event_node || $event_node->bundle() !== 'event') {
      return 0;
    }

    $values = [
      'type' => 'registration', 
      'field_registered_user' => $user_entity->id(),
      'field_event' => $event_node->id(),
    ];

    $registerd = \Drupal::entityTypeManager()
      ->getStorage('register_event')
      ->loadByProperties($values);
    if ($registerd) {
      return 1;
    }
  }

  public function getEventSessions($event_id) {
    $data = [];
    $sessions = \Drupal::entityTypeManager()->getStorage("node")->loadByProperties([
      "field_belongs_to_event"=>$event_id
    ]);
    if ($sessions) {
      /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
      $date_formatter = \Drupal::service('date.formatter');
      foreach ($sessions as $key => $value) {
        $session_start_date = $value->get("field_session_start_date")->value;
        $session_end_date = $value->get("field_session_end_date")->value;
        $data[] = [
        "speaker" => $value->get("field_speaker")->entity->getAccountName(),
        "start_date" => $date_formatter->format(strtotime($session_start_date), 'custom', 'F j, Y, g:i a'),
        "end_date" => $date_formatter->format(strtotime($session_end_date), 'custom', 'F j, Y, g:i a'),
        ];
      }
    }
    return $data;
  }
       
}
