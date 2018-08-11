<?php

namespace Drupal\gendered_text_customizations\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;

class BreadcrumbCustom implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
    $parameters = $attributes->getParameters()->all();
    // I need my breadcrumbs for a few node types ONLY,
    // so it should be applied on node page ONLY.
    if (isset($parameters['node']) && !empty($parameters['node'])) {
      return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link ::createFromRoute('Home', '<front>'));

    $node = \Drupal::routeMatch()->getParameter('node');
    $node_type = $node->bundle();

    switch ($node_type) {

      // If node type is "text".
      // I want to add as parent of breadcrumb my summary text view.
      case 'text':
        $genre = $node->get('field_genre')->getValue();
        $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($genre[0]['target_id']);
        $title = $term->name->value;
        $breadcrumb->addLink(Link ::createFromRoute($title, 'entity.taxonomy_term.canonical', ['taxonomy_term' => $genre[0]['target_id']]));
        break;
    }

    // Don't forget to add cache control by a route,
    // otherwise you will surprice,
    // all breadcrumb will be the same for all pages.
    $breadcrumb->addCacheContexts(['route']);

    return $breadcrumb;
  }

}
