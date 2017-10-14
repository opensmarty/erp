<?php

namespace renk\yiipal\tracker\providers;

use renk\yiipal\tracker\providers\AbstractTracker;

class EUB extends AbstractTracker
{
    protected function sendRequest($number){
        $EUBApi = "TrackV2";
        $EUBUserId = "689JEULI6486";
        $request = 'http://production.shippingapis.com/ShippingAPI.dll?API='.$EUBApi.'&XML=';
        $xml = urlencode('<?xml version="1.0" encoding="UTF-8" ?><TrackRequest USERID="'.$EUBUserId.'"><TrackID ID="'.$number.'"></TrackID></TrackRequest>');
        $request .= $xml;
        $xmldata = file_get_contents($request);
        $track = simplexml_load_string($xmldata);
        $json = json_encode($track);
        $track = json_decode($json,TRUE);
        $response = isset($track['TrackInfo'])?$track['TrackInfo']:false;
        return $response;
    }

    protected function checkDelivered($response){
        if(!isset($response->TrackSummary)){
            return false;
        }

        if (strpos($response->TrackSummary, 'delivered at') !== false) {
            return true;
        }else{
            return false;
        }
    }

    public function getInfoTable($number){
        $output = '<table class="table table-striped table-bordered">';
        $response = $this->sendRequest($number);
        if($response && isset($response['TrackDetail'])){
            if(is_array($response['TrackDetail'])){
                foreach($response['TrackDetail'] as $index => $row){
                    $output.='<tr><td>'.(count($response['TrackDetail'])-$index).'</td><td>'.$row.'</td></tr>';
                }
            }else{
                $output.='<tr><td>'.$response['TrackDetail'].'</td></tr>';
            }
        }else{
            $output.='<tr><td>没有物流信息</td></tr>';
        }

        $output.='</table>';
        return $output;
    }

}
