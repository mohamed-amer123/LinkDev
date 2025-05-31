<?php

namespace Drupal\event\Drush\Commands;

use Drupal\node\Entity\Node;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Drush\Commands\AutowireTrait;

/**
 * A Drush commandfile.
 */
final class EventCommands extends DrushCommands {

  use AutowireTrait;

  /**
   * Constructs an EventCommands object.
   */
  public function __construct() {
    parent::__construct();
  }

  /**
   * Command description here.
   */
  #[CLI\Command(name: 'event:cleanup', aliases: ['cl'])]
  public function cleanup() {
    $timestamp = \strtotime("-1 year");
    $datetime = (new \DateTime())->setTimestamp($timestamp);
    $formatted = $datetime->format('Y-m-d\TH:i:s'); 
    $nids = \Drupal::entityQuery('node')
      ->condition('type', 'event')
      ->condition('field_start_date', $formatted, '<')
      ->accessCheck(FALSE)
      ->execute();
    if (empty($nids)) {
      $this->logger()->notice('No events found to clean up.');
      $this->logger()->success('Event cleanup command finished successfully.');
      return;
    }
    $nodes = Node::loadMultiple($nids);
    foreach ($nodes as $node) {
      try {
        $this->logger()->notice(t('Starting deletion of node ID: @nid', ['@nid' => $node->id()]));
        $node->delete();
        $this->logger()->success(t('Node ID @nid deleted successfully.', ['@nid' => $node->id()]));
      } catch (\Throwable $th) {
        $this->logger()->error(t('Failed to delete node ID @nid. Error: @error', [
          '@nid' => $node->id(),
          '@error' => $th->getMessage(),
        ]));
      }
    }
  }

}
