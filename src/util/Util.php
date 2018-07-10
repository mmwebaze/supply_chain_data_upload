<?php
namespace Drupal\supply_chain_data_upload\util;

use Drupal\node\Entity\Node;

class Util {
    public function createSupplyChainData($file_path){
        $core_path = \Drupal::service('file_system');
        $path = $core_path->realpath($file_path);
        $file = fopen($path,"r");

        $r = 0; // row counter with 0 being first row
        while(! feof($file)){
            $row = fgetcsv($file);

            if ($r == 0){
                $r++;
                continue;
            }
            Node::create($this->createNode($row))->save();
            print($row[0]);
        }
        fclose($file);
    }
    public function createNode($row){

        $nodeData = array(
            'title' => $row[0],
            'type' => 'supply_chain_data',
            'field_country' => ['target_id' => $this->getTerm($row[1], 'countries')],
            'field_program' => ['target_id' => $this->getTerm($row[2], 'programs')],
            'field_estimated_quantity_on_hand' => $row[3],
            'field_month'  => ['target_id' => $this->getTerm($row[4], 'month')],
            'field_year'  => ['target_id' => $this->getTerm($row[5], 'year')],
            'field_months_of_stock' => ['value' => $row[6]],
            'field_amcs' => ['value' => $row[7]],
            'field_min' => ['value' => $row[8]],
            'field_max' => ['value' => $row[9]],
        );

        return $nodeData;
    }
    public function getTerm($name, $vocabulary){
        $termStorage = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term');
        $ids = $termStorage->getQuery()
            ->condition('vid', $vocabulary)
            ->condition('name', $name)->execute();
        $terms = $termStorage->loadMultiple($ids);
        if (count($terms) > 1 || count($terms) < 1){
            //print('No taxonomy associated with zipcode: '.$zipcode."\n");
            return 0;
        }
        return current($terms)->id();
    }
}