<?php

namespace Drupal\form_add_region\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Code\Database\Database;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * form_add_coutry  configuration settings form.
 */

class FormAddRegionSettingsForm extends FormBase {

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
    return 'form_add_region_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['form_add_region.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCountries(){
    $query = \Drupal::database();
    return $query->query("Select countryid, countryenglishname from countries;")->fetchAllKeyed();
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['country'] = [
      '#title' => 'Select a country for a region',
      '#type' => 'select',
      '#options' => $this->getCountries(),
      '#empty_option' => '- Country -'
    ];

    $form['region-english-name'] = [
      '#title' => 'Enter a English name for a region',
      '#type' => 'textfield',
      '#placeholder' => 'Enter a English name for a region'
    ];
    $form['region-native-name'] = [
      '#title' => 'Enter a native name for a country',
      '#type' => 'textfield',
      '#placeholder' => 'Enter a native name for a region'
    ];

    // Create the submit button.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit a region'),
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
    //dpm($values);
	  //Send the form data to the specified table in our database
    $query = \Drupal::database();
    $query->insert('regions')->fields([
      'regionenglishname' =>$values['region-english-name'],
      'regionnativename' =>$values['region-native-name'],
      'countryid' => (int) $values['country'],
    ])->execute();



    //Send a message after submitting the form
    $messenger = \Drupal::messenger();
    $messenger->addMessage('A new region has been added to the database.', $messenger::TYPE_STATUS);

  }

}
