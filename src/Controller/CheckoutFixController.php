<?php

namespace Drupal\commerce_fix_stuck_payment\Controller;

use Drupal\commerce_checkout\Controller\CheckoutController;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Overrides the checkout controller to clear stale tokens with logging.
 */
class CheckoutFixController extends CheckoutController {

  /**
   * Clears the payment method if the user is on the initial info step.
   */
  public function checkAndClear(RouteMatchInterface $route_match) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $route_match->getParameter('commerce_order');
    $step_id = $route_match->getRawParameter('step');
    $order_id = $order ? $order->id() : 'N/A';

    $logger = \Drupal::logger('commerce_fix_stuck_payment');
    $logger->info('CheckoutFix triggered for Order @id at step: @step', [
      '@id' => $order_id,
      '@step' => $step_id,
    ]);

    // Target ONLY the information step. 
    // Removing 'review' ensures we don't kill the token when the user is about to pay.
    $target_steps = ['login', 'order_information'];

    if ($order && in_array($step_id, $target_steps)) {
      $state = $order->getState()->getId();
      
      if ($state == 'draft' && $order->hasField('payment_method')) {
        if (!$order->get('payment_method')->isEmpty()) {
          $payment_method = $order->get('payment_method')->entity;
          
          if ($payment_method) {
            $method_id = $payment_method->id();
            $payment_method->delete();
            
            $logger->notice('Deleted stale payment method @mid for Order @oid at step @step', [
              '@mid' => $method_id,
              '@oid' => $order_id,
              '@step' => $step_id,
            ]);
          }
          
          $order->set('payment_method', NULL);
          $order->save();
          $logger->info('Order @oid payment_method field cleared and saved.', ['@oid' => $order_id]);
        } else {
          $logger->debug('Order @oid has no payment method to clear.', ['@oid' => $order_id]);
        }
      } else {
        $logger->debug('Order @oid skipped: State is @state (expected draft).', [
          '@oid' => $order_id,
          '@state' => $state,
        ]);
      }
    } else if ($order) {
      $logger->info('Skipped clearing for Order @oid at step @step (Protected Step).', [
        '@oid' => $order_id, 
        '@step' => $step_id
      ]);
    }

    return $this->formPage($route_match);
  }
}

