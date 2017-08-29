<?php

namespace Drupal\commerce_order_test\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class MultipleProfileSelectTestForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'multiple_commerce_profile_select_element_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['profile1'] = [
      '#type' => 'commerce_profile_select',
      '#default_value' => NULL,
      '#profile_type' => 'customer',
      '#owner_uid' => \Drupal::currentUser()->id(),
      '#available_countries' => ['TW', 'HU', 'FR', 'US', 'RS', 'DE'],
    ];
    $form['profile2'] = [
      '#type' => 'commerce_profile_select',
      '#default_value' => NULL,
      '#profile_type' => 'customer',
      '#owner_uid' => \Drupal::currentUser()->id(),
      '#available_countries' => ['TW', 'HU', 'FR', 'US', 'RS', 'DE'],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $profile1 = $form_state->getValue('profile1');
    $profile2 = $form_state->getValue('profile2');
    drupal_set_message($this->t('Profile1 selected: :label', [':label' => $profile1->label()]));
    drupal_set_message($this->t('Profile2 selected: :label', [':label' => $profile2->label()]));
  }
}
