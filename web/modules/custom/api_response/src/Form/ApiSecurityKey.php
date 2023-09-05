<?php

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class  for setting the security key form.
 */
class ApiSecurityKey extends ConfigFormBase {
  const CONFIGNAME = "api_response.settings";

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'api_response_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return [
      static::CONFIGNAME,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::CONFIGNAME);
    $form['api_secret_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Secret Key'),
      '#description' => $this->t('Enter the secret key to access the API.'),
      '#default_value' => $config->get('api_secret_key'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(static::CONFIGNAME);
    $config->set('api_secret_key', $form_state->getValue('api_secret_key'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
