<?php

namespace renk\yiipal\tracker\providers;

use renk\yiipal\tracker\providers\AbstractTracker;

class UPS extends AbstractTracker
{
    protected function sendRequest($number){
        $url = 'https://wwwapps.ups.com/WebTracking/detail';
        $query = array();
        $query['loc'] = 'en_US';
        $query['track.x'] = 'Track';
        $query['tracknum'] = $number;
        try{
            $response = $this->url_post($url, $query);
        }catch (\Exception $e){
            $response = false;
        }

        return $response;
    }

    protected function checkDelivered($response){
        $dom = new \DOMDocument;
        @$dom->loadHTML($response);
        $dom->preserveWhiteSpace = false;
        $domxpath = new \DOMXPath($dom);
        $rows = $domxpath->query("//a[@id='tt_spStatus']");
        if($rows->length==0){
            return false;
        }
        $lastRow = new \stdClass();
        foreach ($rows as $row) {
            $lastRow = $row;
        }
        $lastRowText = preg_replace('/\s*/i','',$lastRow->textContent);
        if(strpos($lastRowText, 'Delivered') !== false){
            return true;
        }else{
            return false;
        }
    }

    public function getInfoTable($number){
        $response = $this->sendRequest($number);
        if($response){
            $dom = new \DOMDocument;
            @$dom->loadHTML($response);
            $dom->preserveWhiteSpace = false;
            $domxpath = new \DOMXPath($dom);
            $table = $domxpath->query("//table[@class='dataTable']");
            if($table->length>0){
                $output = $dom->saveHTML($table->item(0));
            }else{
                $output = '<table class="table table-striped table-bordered">';
                $output.='<tr><td>没有物流信息</td></tr>';
                $output.='</table>';
            }
        }else{
            $output = '<table class="table table-striped table-bordered">';
            $output.='<tr><td>没有物流信息</td></tr>';
            $output.='</table>';
        }
        $output = str_replace('dataTable','table table-striped table-bordered',$output);
        return $output;
    }
}
