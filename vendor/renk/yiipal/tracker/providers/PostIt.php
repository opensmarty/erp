<?php

namespace renk\yiipal\trackers\providers;

use renk\yiipal\trakers\providers\AbstractTracker;

class PostIt extends AbstractTracker
{
    protected function sendRequest($number){
        $url = 'http://www.poste.it/online/dovequando/ricerca.do';
        $query = array();
        $query['mpcode1'] = $number;
        $query['mpdate'] = '1';
        $response = $this->url_post($url, $query);
        return $response;
    }

    protected function checkDelivered($response){
        $dom = new \DOMDocument;
        @$dom->loadHTML($response);
        $dom->preserveWhiteSpace = false;
        $domxpath = new \DOMXPath($dom);
        $rows = $domxpath->query("//table[@id='tabella-0']//tbody//tr");
        if($rows->length==0){
            return false;
        }
        $lastRow = new \stdClass();
        foreach ($rows as $row) {
            $lastRow = $row;
        }
        $lastRowText = preg_replace('/\s*/i','',$lastRow->textContent);
        if(strpos($lastRowText, 'Consegnata') !== false){
            return true;
        }else{
            return false;
        }
    }
}
