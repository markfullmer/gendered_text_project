<?php

namespace Drupal\gendered_text_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\gendered_text_api\GenderedText;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\gendered_text_api\Controller\ReplacementMapController;

/**
 * Class Readontroller.
 */
class ReadController extends ControllerBase {

  /**
   * The body to be displayed.
   *
   * @var string
   */
  public $body;

  /**
   * The legend, as determined from the node data OR the GET parameters.
   *
   * @var string
   */
  protected $legend;

  /**
   * Renders prepared text.
   *
   * @return page
   *   Return markup.
   */
  public function read(NodeInterface $node = NULL) {
    $this->legend = self::buildLegend($node);
    $this->processText($node);
    $query_string = \Drupal::request()->getQueryString();
    $form['back'] = ['#markup' => '&#x2190; <a class="btn btn-default" href="' . $node->toUrl()->toString() .  '">Change genders</a>'];
    $form['button'] = ['#markup' => '<a class="btn btn-primary pull-right" href="/export/' . $node->id() . '?' . $query_string . '">Export to eBook</a>'];
    $form['output'] = ['#markup' => '<div class="gendered-text">' . $this->body . '</div>'];
    return $form;
  }

  /**
   * Renders prepared text.
   *
   * @param node
   *   The original node object.
   */
  public static function buildLegend($node) {
    $string = '';
    $map = [
      'f' => 'female',
      'm' => 'male',
      'n' => 'non-binary',
    ];    
    $available_personae = [];
    $i = 0;
    $personae = $node->field_personae->getValue();
    if (empty($personae)) {
      $legend_string = GenderedText::findLegend($node->field_body->getValue()[0]['value']);
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
        $available_personae[$i]['persona'][] = $female;
        $available_personae[$i]['persona'][] = $male;
        $available_personae[$i]['persona'][] = $nonbinary;
        $available_personae[$i]['gender'] = $default;
        $i++;
      }      
    }
    else {
      foreach ($personae as $element) {
        $p = Paragraph::load($element['target_id']);
        $female = $p->field_persona_female->value;
        $male = $p->field_persona_male->value;
        $nonbinary = $p->field_persona_nonbinary->value;
        $default = $p->field_persona_default->value;
        $available_personae[$i]['persona'][] = $female;
        $available_personae[$i]['persona'][] = $male;
        $available_personae[$i]['persona'][] = $nonbinary;
        $available_personae[$i]['gender'] = $default;
        $i++;
      }
    }
    // Get user-supplied overrides to gender map.
    $get = \Drupal::request()->query->all();
    if (!empty($get)) {
      foreach ($get as $persona => $gender) {
        $persona = str_ireplace('_', ' ', $persona);
        foreach ($available_personae as $key => $values) {
          if (in_array(rawurldecode($persona), $values['persona'])) {
            if (isset($map[$gender])) {
              $available_personae[$key]['gender'] = $map[$gender];
            }
          }
        }
      }
    }
    foreach ($available_personae as $key => $values) {
      $string .= '[' . implode('/', $values['persona']) . ':' . $values['gender'] . ']';
    }
    return '[' . $string . ']';
  }

  /**
   * Renders prepared text.
   *
   * @param node
   *   The original node object.
   */
  protected function processText($node) {
    $body_obj = $node->get('field_body');
    $body_raw = $body_obj->getValue()[0]['value'];
    $in_text_legend = GenderedText::findLegend($body_raw);
    // Strip the legend from the output.
    $text = GenderedText::removeLegend($body_raw, $in_text_legend);
    $replacements = ReplacementMapController::getReplacementMap();
    $this->body = GenderedText::process($text . $this->legend, $replacements);
  }

}
