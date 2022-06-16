<?php

namespace Drupal\form_add_country\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Code\Database\Database;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * form_add_coutry  configuration settings form.
 */

class FormAddCountrySettingsForm extends FormBase {

  /**
   * The webform token manager.
   *
   * @var \Drupal\webform\WebformTokenManagerInterface
   */
  protected $tokenManager;

  /**
   * The webform element plugin manager.
   *
   * @var \Drupal\webform\Plugin\WebformElementManagerInterface
   */
  protected $elementManager;
  public int $regionid;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->tokenManager = $container->get('webform.token_manager');
    $instance->elementManager = $container->get('plugin.manager.webform.element');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_add_country_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['form_add_country.settings'];
  }

  /**
   * {@inheritdoc}
   */

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['country-english-name'] = [
      '#title' => 'Enter a English name for a country',
      '#type' => 'textfield',
      '#placeholder' => 'Enter a English name for a country'
    ];
    $form['country-native-name'] = [
      '#title' => 'Enter a native name for a country',
      '#type' => 'textfield',
      '#placeholder' => 'Enter a native name for a country'
    ];

    // Create the submit button.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit a country'),
    ];

    return $form;
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
    // Get all values.
    $values = $form_state->getValues();

	  //Send the form data to the specified table in our database
    $query = \Drupal::database();
    $query->insert('countries')->fields([
      //'countryid' => 6,
      'countryenglishname' =>$values['country-english-name'],
      'countrynativename' =>$values['country-native-name'],
    ])->execute();

    //Send a message after submitting the form
    $messenger = \Drupal::messenger();
    $messenger->addMessage('A new country has been added to the database.', $messenger::TYPE_STATUS);

  }

}
