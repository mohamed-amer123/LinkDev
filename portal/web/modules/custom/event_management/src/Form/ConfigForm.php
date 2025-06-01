<?php

declare(strict_types=1);

namespace Drupal\event_management\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Event management settings for this site.
 */
final class ConfigForm extends ConfigFormBase {

  public const CONFIGNAME = "event_management.settings";

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'event_management_config';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [self::CONFIGNAME];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config(self::CONFIGNAME);
    $form["events_display"] = [
      "#type" => "checkbox",
      "#title" => $this->t("Show/Hide past events"),
      "#description" => $this->t("By select this option events all previous events will be displayed."),
      '#default_value' => $config->get('events_display')
    ];
    $form["event_number"] = [
      "#type" => "number",
      "#title" => $this->t("Displayed number"),
      "#description" => $this->t("Number of events to be displayed to users 0 means default view number."),
      '#default_value' => $config->get('event_number'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if ($form_state->getValue('example') === 'wrong') {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('The value is not correct.'),
    //     );
    //   }
    // @endcode
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config_values = ["events_display","event_number"];
    $config = $this->config(self::CONFIGNAME);
    foreach ($form_state->getValues() as $key => $value) {
      if (in_array($key,$config_values)) {
        $this->log_changes($form_state, $key);
        $config->set($key, $value);
      }
    }
    $config->save();
    // Clear cache after change config values to make menu change dynamicly and list card for events .
    \Drupal::cache('menu')->deleteAll();
    \Drupal::service('router.builder')->rebuild();
    \Drupal::service('cache_tags.invalidator')->invalidateTags([
      'rendered',
      'config:system.menu.main',
    ]);
    parent::submitForm($form, $form_state);
  }

  /**
   * Log changes to custom table log_configuration.
   */
  function log_changes(FormStateInterface $form_state, $key){
    $old_value = $this->config(self::CONFIGNAME)->get($key);
    $new_value = $form_state->getValue($key);
    $table_name = "log_configuration";
    if ($old_value != $new_value) {
      \Drupal::database()->insert($table_name)
      ->fields([
        'uid' => \Drupal::currentUser()->id(),
        'config_name' => self::CONFIGNAME,
        'key' => $key,
        'old_value' => is_bool($old_value) ? ($old_value ? '1' : '0') : (string) $old_value,
        'new_value' => is_bool($new_value) ? ($new_value ? '1' : '0') : (string) $new_value,
        'created' => \Drupal::time()->getCurrentTime(),
      ])
      ->execute();
    }
  }
}
