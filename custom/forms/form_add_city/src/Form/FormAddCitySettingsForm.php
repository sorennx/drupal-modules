<?php

namespace Drupal\form_add_city\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Code\Database\Database;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * form_add_coutry  configuration settings form.
 */

class FormAddCitySettingsForm extends FormBase {

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
    return 'form_add_city_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['form_add_city.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCountries(){
    $query = \Drupal::database();
    return $query->query("Select countryid, countryenglishname from countries;")->fetchAllKeyed();
  }

//  public function getRegionByCountry(countryId){
//    $query = \Drupal::database();
//    return $query->query("Select regionid, regionenglishname from regions where countryid = '$countryId';")->fetchAllKeyed();
//
//  }

  public function countryAjaxCallback(array &$form, FormStateInterface $form_state) {
    $query = \Drupal::database();
    if ($selectedValue = $form_state->getValue('country')) {
      $countryId = $form_state->getValue('country');
      $regionList = $query->query("Select regionid, regionenglishname from regions where countryid = '$countryId';")->fetchAllKeyed();
      //dpm($regionList);
      $form['region']['#options'] = $regionList;

    }


    // Return the prepared select field.

    return $form['region'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['country'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a country of your university'),
      '#options' =>$this->getCountries(),
      '#empty_option'=>$this->t("- Country -"),
      '#validated' => TRUE,
      '#ajax' => [
        'callback' =>'::countryAjaxCallback',
        'event' => 'change',
        'wrapper' => 'edit-region', // This element is updated with this AJAX callback.
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Verifying entry...'),
        ],
      ],

    ];

    $form['region'] =[
      '#type'=>'select',
      '#title'=> $this->t('Select a region'),
      '#options' =>array(), // powinna tu byc pusta lista, miasta z jakiegos powodu wtedy pokazuja sie tylko z tego kraju (o ile go wpiszemy)
      '#empty_option'=>$this->t("- Region -"),

      '#prefix' => '<div id="edit-region">',
      '#suffix' => '</div>',
      '#validated' => TRUE,
    ];

    $form['city-english-name'] = [
      '#title' => 'Enter a English name for a city',
      '#type' => 'textfield',
      '#placeholder' => 'Enter a English name for a city'
    ];
    $form['city-native-name'] = [
      '#title' => 'Enter a native name for a city',
      '#type' => 'textfield',
      '#placeholder' => 'Enter a native name for a city'
    ];

    // Create the submit button.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit a city'),
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
    $query->insert('cities')->fields([
      'regionid' => (int)$values['region'],
      'cityenglishname' =>$values['city-english-name'],
      'citynativename' =>$values['city-native-name'],
    ])->execute();

    //Send a message after submitting the form
    $messenger = \Drupal::messenger();
    $messenger->addMessage('A new city has been added to the database.', $messenger::TYPE_STATUS);

  }

}
