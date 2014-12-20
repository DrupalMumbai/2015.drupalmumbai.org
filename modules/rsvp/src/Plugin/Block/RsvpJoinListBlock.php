<?php

/**
 * @file
 * Contains \Drupal\rsvp\Plugin\Block\RsvpJoinListBlock.
 */

namespace Drupal\rsvp\Plugin\Block;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides a 'Rsvp User List' block.
 *
 * @Block(
 *   id = "rsvp_join_list_block",
 *   admin_label = @Translation("Rsvp User List"),
 *   category = @Translation("Forms")
 * )
 */
class RsvpJoinListBlock extends BlockBase {

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
    $items = array();
    $node = \Drupal::routeMatch()->getParameter('node');
    $all_users = db_select('rsvp_user_list', 'rsvp')
      ->fields('rsvp', array('rsvp_id', 'user_id'))
      ->condition('node_id', $node->id())
      ->condition('status', 1)
      ->orderBy('rsvp_id', 'DESC')
      ->execute()
      ->fetchAll();

    foreach ($all_users as $key => $user) {
      $account = user_load($user->user_id, TRUE);
      $items = array($account->get('name')->value);
    }
    return array('#theme' => 'item_list', '#items' => $items);
  }
}
