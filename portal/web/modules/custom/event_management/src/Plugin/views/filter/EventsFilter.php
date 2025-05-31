<?php

namespace Drupal\event_management\Plugin\views\filter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter to include nodes based on a boolean field value.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("filter_by_role")
 */
class EventsFilter extends FilterPluginBase {

  /**
   * Entity type manager service.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructs a PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * Provide simple equality operator.
   */
  public function operatorOptions(): array {
    return [
      '=' => $this->t('Matched products.'),
    ];
  }

  /**
   * Provide empty form.
   */
  public function valueForm(&$form, FormStateInterface $form_state): void {
    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t("No configuration needed for this filter."),
    ];
  }

  /**
   * Check what current user have access to.
   */
  public function query(): void {
    // Check is not admin role.
    $user_roles = \Drupal::currentUser()->getRoles();
    $configuration = [
      'left_table' => 'node_field_data',
      'left_field' => 'nid',
      'table' => 'node__field_category_of_event',
      'field' => 'entity_id',
    ];
    $join = \Drupal::service('plugin.manager.views.join')->createInstance('standard', $configuration);
    $this->query->addRelationship('node__field_category_of_event', $join, 'node_field_data');
    $terms = \Drupal::entityTypeManager()->getStorage("taxonomy_term")->loadByProperties(['vid' => 'event_category']);
    foreach ($terms as $key => $value) {
      if (\strtolower($value->getName()) == "public") {
        $public_id = $key;
      } elseif (\strtolower($value->getName()) == "private"){
        $private_id = $key;
      } else {
        $limited_id = $key;
      }
    }
    if (\Drupal::currentUser()->isAnonymous()) {
      $this->query->addWhereExpression($this->options['group'], 'node__field_category_of_event.field_category_of_event_target_id = (:values)', [':values' => $public_id]);
    }elseif (!in_array("administrator",$user_roles) || !in_array("organizer_role",$user_roles)) {
      $this->query->addWhereExpression($this->options['group'], 'node__field_category_of_event.field_category_of_event_target_id IN (:values[])', [':values[]' => [$public_id,$limited_id]]);
    }

  }

  /**
   * Skip validation if no options chosen we can use it as a non-filter.
   */
  public function validate(): void {
    if (!empty($this->value)) {
      parent::validate();
    }
  }

}
