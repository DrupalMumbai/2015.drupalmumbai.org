<?php

/**
 * @file
 * Contains \Drupal\rsvp\Plugin\Block\RsvpBlock.
 */

namespace Drupal\rsvp\Plugin\Block;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides a 'Rsvp form' block.
 *
 * @Block(
 *   id = "rsvp_form_block",
 *   admin_label = @Translation("Rsvp form"),
 *   category = @Translation("Forms")
 * )
 */
class RsvpBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return $account->hasPermission('access content');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\rsvp\Form\RsvpBlockForm');
  }

}
