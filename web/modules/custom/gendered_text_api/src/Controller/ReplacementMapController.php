<?php

namespace Drupal\gendered_text_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\gendered_text_api\GenderedText;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Term;  

/**
 * Class ReplacementMapController.
 */
class ReplacementMapController extends ControllerBase {

  /**
   * Display the replacement map in a human-readable format.
   */
  public function list() {
    $header = ['Type','Female', 'Male', 'Non-Binary'];
    $output['header']['#markup'] = '<a href="/admin/structure/taxonomy/manage/replacement_map/overview">Edit</a>';

    $maps = self::getReplacementMap(FALSE, FALSE);
    foreach ($maps as $map => $values) {
        $data = [];
        $transformed = self::transformReplacements($values);
        ksort($transformed);
        foreach ($transformed as $key => $values) {
            if (!empty($values['female']) && !empty($values['male'])) {
                if (empty($values['non-binary'])) {
                    $values['non-binary'] = $values['female'];
                }
                $data[] = [$key, $values['female'], $values['male'], $values['non-binary']];
            }
            
        }
        $output[$map] = array(
            '#theme' => 'table',
            '#caption' => $map,
            '#header' => $header,
            '#rows' => $data,
        );
    }
    
    return $output;
  }

  /**
   * Create a mapping from $replacements.
   *
   * @return array
   *   A traversable array of parts of speech.
   */
  public static function transformReplacements($replacements) {
    $map = [];
    foreach ($replacements as $id => $attributes) {
      $pos = $attributes['pos'];
      $gender = $attributes['gender'];
      $output = !empty($attributes['output']) ? $id . ' (' . $attributes['output'] . ')' : $id;
      $map[$pos][$gender] = $output;
    }
    return $map;
  }  

  /**
   * Get replacement map from local file.
   */
  public static function getReplacementMap($static = FALSE, $return_flat = TRUE) {
    if ($static) {
        $module_handler = \Drupal::service('module_handler');
        $module_path = $module_handler->getModule('gendered_text_api')->getPath();
        $file_location = DRUPAL_ROOT . '/' . $module_path . '/data/replacements.json';
        $file = fopen($file_location, "r") or die("Unable to open file!");
        $json = fread($file, filesize($file_location));
        fclose($file);
        return (array) json_decode($json, TRUE);
    }
    else {
        $vocabulary_name = 'replacement_map'; //name of your vocabulary
        $query = \Drupal::entityQuery('taxonomy_term');
        $query->condition('vid', $vocabulary_name);
        $query->sort('weight');
        $tids = $query->execute();
        $maps = Term::loadMultiple($tids);
        $flat = [];
        foreach($maps as $map) {
            //$name = $term->getName();;
            $category = $map->get('field_replacement_item');
            $terms = $category->getValue();
            $output = [];
            foreach ($terms as $element) {
                $p = Paragraph::load( $element['target_id'] );
                $type = $p->field_replacement_type->value;
                $female = $p->field_replacement_female->value;
                $female_display = $p->field_replacement_female_display->value;
                $male = $p->field_replacement_male->value;
                $male_display = $p->field_replacement_male_display->value;
                $nonbinary = $p->field_replacement_nonbinary->value;
                $nonbinary_display = $p->field_replacement_nb_display->value;
                $output[$female] = ['gender' => 'female', 'pos' => $type];
                $flat[$female] = ['gender' => 'female', 'pos' => $type];
                $output[$male] = ['gender' => 'male', 'pos' => $type]; 
                $flat[$male] = ['gender' => 'male', 'pos' => $type]; 
                $output[$nonbinary] = ['gender' => 'non-binary', 'pos' => $type];
                $flat[$nonbinary] = ['gender' => 'non-binary', 'pos' => $type];
                if ($female_display) {
                    $output[$female]['output'] = $female_display;
                    $flat[$female]['output'] = $female_display;
                }  
                if ($male_display) {
                    $output[$male]['output'] = $male_display;
                    $flat[$male]['output'] = $male_display;
                }                                                  
                if ($nonbinary_display) {
                    $output[$nonbinary]['output'] = $nonbinary_display;
                    $flat[$nonbinary]['output'] = $nonbinary_display;
                }    
            }
            $all_maps[$map->getName()] = $output;
        }
        if ($return_flat) {
            return $flat;
        }
        return $all_maps;
    }
  }

}
