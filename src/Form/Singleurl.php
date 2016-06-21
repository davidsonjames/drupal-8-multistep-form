<?php

namespace Drupal\multistep\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides multistep single url form.
 */
class Singleurl extends FormBase {

  /**
   * Current step
   * @var int
   */
  protected $step = '';

  /**
   * Steps for the form
   * @var array
   */
  protected $steps = array();

  /**
   * Store values between steps
   * @var array
   */
  protected $multistep_values = array();

  /**
   * Is the form finished?
   * @var bool
   */
  protected $complete = FALSE;

  /**
   * Singleurl constructor.
   */
  public function __construct() {
    $this->steps = array(
      1 => 'room',
      2 => 'service',
      3 => 'review',
    );

    if (!$this->step) {
      $this->step = $this->steps[1];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'multistep_singleurl';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Set values from all step submissions.
    $form_state->setValues($this->multistep_values);

    // Get the step method
    $method = 'step' . ucwords($this->step);

    if (method_exists($this, $method)) {
      $this->{$method}($form, $form_state);
    }
    else {
      exit('You have not created a method ' . $method);
    }

    return $form;
  }

  /**
   * First step of form.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  private function stepRoom(array &$form, FormStateInterface $form_state) {
    $rating = $form_state->getValue('room_rating');

    $form['rate_the_room']['room_rating'] = array(
      '#type' => 'radios',
      '#title' => 'How would you rate the room you stayed in?',
      '#required' => TRUE,
      '#options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5),
      '#default_value' => $rating ? $rating : NULL,
    );

    $form['next'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Next'),
    );
  }

  /**
   * Second step of form
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  private function stepService(array &$form, FormStateInterface $form_state) {
    $rating = $form_state->getValue('service_rating');

    $form['rate_the_service']['service_rating'] = array(
      '#type' => 'radios',
      '#title' => 'How would you rate our service?',
      '#required' => TRUE,
      '#options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5),
      '#default_value' => $rating ? $rating : NULL,
    );

    $form['actions']['back'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#id' => 'back',
      '#validate' => array(),
      '#limit_validation_errors' => array(),
      '#submit' => array(),
    );

    $form['actions']['next'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#id' => 'next',
    );
    
    $form['#submit'] = array(

    );
  }

  /**
   * Review step of form
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  private function stepReview(array &$form, FormStateInterface $form_state) {

    $room = $form_state->getValue('room_rating');
    $service = $form_state->getValue('service_rating');
    $email = $form_state->getValue('email');

    $form['review']['review_details'] = array(
      '#type' => 'table',
      '#rows' => array(
        array('Room Rating', $room),
        array('Service Rating', $service),
      ),
    );

    $form['review']['email'] = array(
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#description' => $this->t('Enter your email address and you\'ll be entered into a prize draw'),
      '#default_value' => $email ? $email : NULL,
    );

    $form['actions']['back'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#id' => 'back',
      '#validate' => array(),
      '#limit_validation_errors' => array(),
      '#submit' => array(),
    );

    $form['actions']['next'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#id' => 'next',
    );
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->multistep_values = $form_state->getValues() + $this->multistep_values;
    $step_key = array_search($this->step, $this->steps);

    // Get the step method
    $method = 'step' . ucwords($this->step) . 'Submit';

    if (method_exists($this, $method)) {
      $this->{$method}($form, $form_state);
    }

    if ($this->complete) {
      return;
    }

    if ($form_state->getTriggeringElement()['#id'] == 'back') {
      // Move to previous step
      $this->step = $this->steps[$step_key - 1];
    }
    else {
      // Move to next step.
      $this->step = $this->steps[$step_key + 1];
    }

    $form_state->setRebuild();
  }
  
  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function stepReviewSubmit(array &$form, FormStateInterface &$form_state) {
    drupal_set_message($this->t('Thank you for your submission'));
    $form_state->setRedirect('<front>');
    $this->complete = TRUE;
  }

}
