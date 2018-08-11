<?php

namespace Drupal\gendered_text_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Routing\RouteSubscriber;
use Drupal\Core\Cache\Cache;

/**
 * Provides a 'SelectorBlock' block.
 *
 * @Block(
 *  id = "selector_block",
 *  admin_label = @Translation("Gender Selector"),
 * )
 */
class SelectorBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\node\Routing\RouteSubscriber definition.
   *
   * @var \Drupal\node\Routing\RouteSubscriber
   */
  protected $nodeRouteSubscriber;
  /**
   * Constructs a new SelectorBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        RouteSubscriber $node_route_subscriber
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeRouteSubscriber = $node_route_subscriber;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('node.route_subscriber')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      'form' => \Drupal::formBuilder()->getForm('\Drupal\gendered_text_api\Form\SelectorForm'),
    ];
  }

  public function getCacheTags() {
    //With this when your node change your block will rebuild
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      //if there is node add its cachetag
      return Cache::mergeTags(parent::getCacheTags(), array('node:' . $node->id()));
    } else {
      //Return default tags instead.
      return parent::getCacheTags();
    }
  }

  public function getCacheContexts() {
    //if you depends on \Drupal::routeMatch()
    //you must set context of this block with 'route' context tag.
    //Every new route this block will rebuild
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }
  
}
