<?php

namespace renk\yiipal\trackers\providers;

use renk\yiipal\trakers\providers\AbstractTracker;

class Fedex extends AbstractTracker
{
    protected function sendRequest($number){
        $url = 'https://www.fedex.com/trackingCal/track';
        $query = array();
        $data = '{"TrackPackagesRequest":{"appType":"WTRK","uniqueKey":"","processingParameters":{},"trackingInfoList":[{"trackNumberInfo":{"trackingNumber":"'.$number.'","trackingQualifier":"","trackingCarrier":""}}]}}';
        $query['data'] = $data;
        $query['action'] = 'trackpackages';
        $query['locale'] = 'en_CN';
        $query['version'] = '1';
        $query['format'] = 'json';

        $response = $this->url_post($url, $query);
        $response = json_decode($response);
        return $response;
    }

    protected function checkDelivered($response){
        if(isset($response->TrackPackagesResponse) && isset($response->TrackPackagesResponse->packageList)){
            if($response->TrackPackagesResponse->packageList[0]->isDelivered == '1'){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

}
