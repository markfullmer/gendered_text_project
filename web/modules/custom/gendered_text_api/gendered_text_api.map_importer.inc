<?php

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Term;  

function _gendered_text_api_map_import() {
    require_once 'replacement_map.inc';

    $pronouns = ['subject','object','determiner','possessive','reflexive'];
    $opposites = ['opposite1','opposite2','opposite3','opposite4','opposite5','opposite6','opposite7','opposite8','opposite9','opposite10','opposite11','opposite12','opposite13','opposite14','opposite15','opposite16','opposite17','opposite18','opposite 19','opposite 20','opposite 21','opposite 22'];

    $php = json_decode($map, TRUE);
    $final = [];
    $data = $php['feed']['entry'];
    foreach ($data as $key => $datum) {
        if ($key > 2) {
            $id = $datum['gsx$_cn6ca']['$t'];
            $final[$id]['female'] = $datum['gsx$idtobesearchedinthetext']['$t'];
            $final[$id]['male'] = $datum['gsx$_cpzh4']['$t'];
            if (!empty($datum['gsx$_cre1l'])) {
                $final[$id]['nonbinary'] = $datum['gsx$_cre1l']['$t'];
            }
            else {
                $final[$id]['nonbinary'] = $final[$key]['female'];
            }
            if (!empty($datum['gsx$displayifdifferingfromkey']) && $datum['gsx$displayifdifferingfromkey']['$t'] != '') {
                $final[$id]['female_display'] = $datum['gsx$displayifdifferingfromkey']['$t'];
            }
            if (!empty($datum['gsx$_ciyn3'])) {        
                $final[$id]['male_display'] = $datum['gsx$_ciyn3']['$t'];
            }
            if (!empty($datum['gsx$_ckd7g'])) {    
                $final[$id]['nonbinary_display'] = $datum['gsx$_ckd7g']['$t'];
            }
        }
    }

    $map = \Drupal\taxonomy\Entity\Term::load('31');
    foreach($final as $id => $item) {
        if (!in_array($id, $opposites) && !in_array($id, $pronouns)) {
            $paragraph = Paragraph::create([
                'type' => 'replacement_item',
                'field_replacement_type' => array(
                    "value"  =>  $id,
                ),        
                'field_replacement_female' => array(
                    "value"  =>  $item['female'],
                ),
                'field_replacement_female_display' => array(
                    "value"  =>  $item['female_display'],
                ),        
                'field_replacement_male' => array(
                    "value"  =>  $item['male'],
                ),
                'field_replacement_male_display' => array(
                    "value"  =>  $item['male_display'],
                ), 
                'field_replacement_nonbinary' => array(
                    "value"  =>  $item['nonbinary'],
                ),
                'field_replacement_nb_display' => array(
                    "value"  =>  $item['nonbinary_display'],
                ),
            ]);
            $paragraph->save();	
            $map->field_replacement_item->appendItem($paragraph);
            $map->save();
        }
    }
   
}