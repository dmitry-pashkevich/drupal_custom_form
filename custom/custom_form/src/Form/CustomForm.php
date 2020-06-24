<?php
namespace Drupal\custom_form\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class CustomForm extends FormBase {

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => true
    ];

    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => true
    ];

    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#required' => true
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#required' => true
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => true
    ];


    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send Form'),
    ];

    return $form;
  }


  public function getFormId() {
    return 'custom_form';
  }


  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue("email");
    $validateEmail = "/[.][a-z]{2}/";

    if(!preg_match($validateEmail, $email)) {
      $form_state->setErrorByName('email', 'Error Email');
    }
  }


  public function submitForm(array &$form, FormStateInterface $form_state) {

    $firstName = $form_state->getValue('first_name');
    $lastName = $form_state->getValue('last_name');
    $subject = $form_state->getValue('subject');
    $message = $form_state->getValue('message');
    $email = $form_state->getValue('email');
    $apiKey = "b861a982-5597-49c6-8229-529693f34230";
    $toEmail = 'bigmuravei@list.ru';
    $sendMail = mail($toEmail, $subject, $message);

    if($sendMail)  Drupal::logger('ex_form')->notice("Mail is Sent. Mail: {$email}");

    $arr = array(
      'properties' => array(
        array(
          'property' => 'email',
          'value' => $email
        ),
        array(
          'property' => 'firstname',
          'value' => $firstName
        ),
        array(
          'property' => 'lastname',
          'value' => $lastName
        )
      )
    );

    $post_json = json_encode($arr);
    $url = 'https://api.hubapi.com/contacts/v1/contact?hapikey=' . $apiKey;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $this->messenger()->addStatus("Form Submitted");
  }

}
