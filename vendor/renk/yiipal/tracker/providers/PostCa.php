<?php

namespace renk\yiipal\trackers\providers;

use renk\yiipal\trakers\providers\AbstractTracker;

class PostCa extends AbstractTracker
{
    protected function sendRequest($number){
        $url = 'https://www.17track.net/restapi/handlertrack.ashx';
        $query = array();
        $query['{"guid":"d7a102fb2ef943c690b0e6ead6a88a1b","data":[{"num":"9500110585316069449477","fc":"21051"}]}'] = '';
        $query['guid'] = 'd7a102fb2ef943c690b0e6ead6a88a1b';
        $query['data'] = '[{"num":"9500110585316069449477","fc":"21051"}]';
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
        $lastRowText = preg_replace('/\s*/i','',$rows[$rows->length-1]->textContent);
        if(strpos($lastRowText, 'Consegnata') !== false){
            return true;
        }else{
            return false;
        }
    }
}
