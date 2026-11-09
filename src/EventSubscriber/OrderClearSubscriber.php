<?php

namespace Drupal\commerce_fix_stuck_payment\EventSubscriber;

use Drupal\commerce_checkout\Event\CheckoutEvents;
use Drupal\commerce_checkout\Event\CheckoutReopenEvent;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Cleans up "stale" payment methods when a user navigates back in checkout.
 */
class OrderClearSubscriber implements EventSubscriberInterface {

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a new OrderClearSubscriber object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory) {
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Explicitly use the event string since the constant is deprecated or removed.
    return [
      'commerce_checkout.reopen' => 'onCheckoutReopen',
    ];
  }

  /**
   * Removes the payment method if the user returns to an earlier step.
   *
   * @param \Drupal\commerce_checkout\Event\CheckoutReopenEvent $event
   *   The checkout reopen event.
   */
  public function onCheckoutReopen(CheckoutReopenEvent $event) {
    $order = $event->getOrder();
    
    // Check if the order has a payment method reference.
    if ($order->hasField('payment_method') && !$order->get('payment_method')->isEmpty()) {
      $payment_method = $order->get('payment_method')->entity;

      if ($payment_method) {
        // Delete the actual payment method entity (the database row).
        $payment_method->delete();
      }
      
      // Clear the reference on the order and save.
      $order->set('payment_method', NULL);
      $order->save();
      
      $this->loggerFactory->get('commerce_fix')
        ->notice('Successfully cleared stale payment method for Order @id.', ['@id' => $order->id()]);
    }
  }
}

