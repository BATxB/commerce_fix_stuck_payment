<?php

namespace Drupal\commerce_fix_stuck_payment\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('commerce_checkout.form')) {
      // REMOVE the leading backslash here:
      $route->setDefault('_controller', 'Drupal\commerce_fix_stuck_payment\Controller\CheckoutFixController::checkAndClear');
    }
  }
}

