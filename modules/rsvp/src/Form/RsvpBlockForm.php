<?php

/**
 * @file
 * Contains \Drupal\rsvp\Form\RsvpBlockForm.
 */

namespace Drupal\rsvp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Builds the search form for the search block.
 */
class RsvpBlockForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rsvp_block_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $account = \Drupal::currentUser();
    $node = \Drupal::routeMatch()->getParameter('node');

    $form['node_id'] = array(
      '#type' => 'hidden',
      '#default_value' => $node->id(),
    );

    $form['user_id'] = array(
      '#type' => 'hidden',
      '#default_value' => $account->id(),
    );

    $form['actions'] = array('#type' => 'actions');
    if ($this->checkRsvpStatus($account->id(), $node->id())) {
      $form['actions']['cancel'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      );
    }
    else {
      $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Join'),
      );
    }
    return $form;
  }

/**
  * {@inheritdoc}
  */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $uid = $form_state->getValue('user_id');
    $nid = $form_state->getValue('node_id');
    if ($this->checkRsvpStatus($uid, $nid) && $form_state->getValue('op') == 'Join') {
      $form_state->setErrorByName('', $this->t('You have already rsvp this.'));
    }
  }

  public function checkRsvpStatus($uid, $nid) {
    $flag = false;
    $submit = $this->loadRsvp($uid, $nid);
    return (empty($submit) || $submit['status'] ==0) ? FALSE : TRUE;
  }

  public function loadRsvp($uid, $nid) {
    $previous_submit = array();
    $previous_submit = db_select('rsvp_user_list', 'rsvp')
      ->fields('rsvp', array('rsvp_id', 'status'))
      ->condition('rsvp.user_id', $uid)
      ->condition('rsvp.node_id', $nid)
      ->execute()
      ->fetchAssoc();
    return $previous_submit;
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // The transaction opens here.
    $txn = db_transaction();
    $uid = $form_state->getValue('user_id');
    $nid = $form_state->getValue('node_id');
    $submit = $this->loadRsvp($uid, $nid);
    $op = $form_state->getValue('op');

    if ($submit['rsvp_id'] == '') {
      try {
        $rsvp_id = db_insert('rsvp_user_list')
          ->fields(array(
            'user_id' => $uid,
            'node_id' => $nid,
            'status' => 1,
            'created' => REQUEST_TIME,
            'changed' => REQUEST_TIME,
          ))
          ->execute();
      }
      catch (Exception $e) {
        // Something went wrong somewhere, so roll back now.
        $txn->rollback();
        // Log the exception to watchdog.
        watchdog_exception('rsvp', $e);
      }
    }
    else {
      db_update('rsvp_user_list')
        ->condition('rsvp_id', $submit['rsvp_id'])
        ->fields(array(
          'status' => ($op=='Join') ? 1 : 0,
          'changed' => REQUEST_TIME,
        ))
        ->execute();
    }
  }
}
