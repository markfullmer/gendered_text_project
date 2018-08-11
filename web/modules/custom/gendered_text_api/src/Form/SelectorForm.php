<?php

namespace Drupal\gendered_text_api\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\gendered_text_api\GenderedText;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Routing\RouteSubscriber;

/**
 * Class SelectorForm.
 */
class SelectorForm extends FormBase {

  /**
   * Drupal\node\Routing\RouteSubscriber definition.
   *
   * @var \Drupal\node\Routing\RouteSubscriber
   */
  protected $nodeRouteSubscriber;
  /**
   * Constructs a new SelectorForm object.
   */
  public function __construct(
    RouteSubscriber $node_route_subscriber
  ) {
    $this->nodeRouteSubscriber = $node_route_subscriber;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('node.route_subscriber')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'selector_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $form = [];
      $map = [
        'female' => 'f',
        'male' => 'm',
        'nonbinary' => 'n',
      ];
      $nid = $node->id();
      $form['nid'] = [
        '#type' => 'hidden',
        '#value' => $nid,
      ];
      $form['#attached']['library'][] = 'gendered_text_api/autoselect';
      $form['all']['#markup'] = 
        '<p><span class="btn btn-default" id="female">All Female</span> <span class="btn btn-default" id="male">All Male</span> <span class="btn btn-default" id="randomize">Randomize</span></p>';
      $personae = $node->field_personae->getValue();
      if (empty($personae)) {
        $legend_string = GenderedText::findLegend($node->field_body->getValue()[0]['value']);
        preg_match("|\[(.*)\]|", $legend_string, $no_brackets);
        preg_match_all("|\[[^\]]*\]|", $no_brackets[1], $items);
        foreach ($items[0] as $item) {
          preg_match("/\[(.*)\/(.*)\/(.*):(.*)\]/", $item, $output);
          $female = $output[1];
          $male = $output[2];
          if (empty($output[3])) {
            $nonbinary = $female;
          }
          else {
            $nonbinary = $output[3];
          }
          if (empty($output[4])) {
            $default = 'female';
          }
          else {
            $default = $output[4];
          }
          $array = [
            'female' => $female,
            'male' => $male,
            'nonbinary' => $nonbinary,
          ];
          $name = $array[$default];
          $safename = str_ireplace(' ', '_', $name);
          $form[$safename] = [
            '#type' => 'select',
            '#title' => $this->t('Gender for @name', ['@name' => $name]),
            '#options' => ['f' => $this->t('Female'), 'm' => $this->t('Male'), 'n' => $this->t('Non-Binary')],
            '#size' => 0,
            '#default_value' => $map[$default],
          ];
        }
      }
      else { 
        foreach ($personae as $element) {
          $p = \Drupal\paragraphs\Entity\Paragraph::load($element['target_id']);
          $female = $p->field_persona_female->value;
          $male = $p->field_persona_male->value;
          $nonbinary = $p->field_persona_nonbinary->value;
          $default = $p->field_persona_default->value;
          $namekey = 'field_persona_' . $default;
          $safename = str_ireplace(' ', '_', $p->$namekey->value);
          $form[$safename] = [
            '#type' => 'select',
            '#title' => $this->t('Gender for @name', ['@name' => $safename]),
            '#options' => ['f' => $this->t('Female'), 'm' => $this->t('Male'), 'n' => $this->t  ('Non-Binary')],
            '#size' => 0,
            '#default_value' => $map[$default],
          ];
        }
      }
    }
    else {
      return '';
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Read the text'),
      '#attributes' => ['class' => ['btn', 'btn-primary']],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    $selections = [];
    $nid = 0;
    foreach ($form_state->getValues() as $key => $value) {
      if (!in_array($key, ['submit', 'form_build_id', 'form_id', 'op', 'form_token', 'nid'])) {
        $key = str_ireplace('_', ' ', $key);
        $selections[$key] = $value;
      }
      if ($key == 'nid') {
        $nid = $value;
      }
    }
    $form_state->setRedirect('gendered_text_api.read', ['node' => $nid], array('query' => $selections));
    return;
  }

}
