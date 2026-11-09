<?php

namespace Drupal\commerce_fix_stuck_payment\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listening for commerce_checkout.form
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('commerce_checkout.form')) {
      $route->setDefault('_controller', 'Drupal\commerce_fix_stuck_payment\Controller\CheckoutFixController::checkAndClear');
    }
  }
}

