<?php

namespace Drupal\opendatasoft\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ServiceSettingsForm.
 */
class ServiceSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'opendatasoft.servicesettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'opendatasoft_service_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('opendatasoft.servicesettings');

    $wsUrl = $config->get('ws_url');
    $form['ws_url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('API base Url'),
        '#default_value' => $wsUrl,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('opendatasoft.servicesettings')
      ->set('ws_url', $form_state->getValue('ws_url'))
      ->save();
  }

}
