<?php

namespace renk\yiipal\tracker\providers;

use renk\yiipal\tracker\providers\AbstractTracker;

class DHL extends AbstractTracker
{
    protected function sendRequest($number){
        $track = file_get_contents("http://www.cn.dhl.com/shipmentTracking?AWB=".$number."&countryCode=cn&languageCode=en&_=".time());
        $response = json_decode($track);
        return $response;
    }

    protected function checkDelivered($response){
        if(!isset($response->results)){
            return false;
        }
        $result = $response->results[0];
        if(isset($result->delivery) && $result->delivery->status=='delivered'){
            return true;
        }else{
            return false;
        }
    }

    public function getInfoTable($number){
        $output = '<table class="table table-striped table-bordered">';
        $response = $this->sendRequest($number);
        if(isset($response->results) && isset($response->results[0]->checkpoints)){
            foreach($response->results[0]->checkpoints as $row){
                $output.='<tr><td>'.$row->counter.'</td><td>'.$row->time.' '.$row->date.'</td><td>'.$row->location.'</td><td>'.$row->description.'</td></tr>';
            }
        }else{
            $output.='<tr><td>没有物流信息</td></tr>';
        }
        $output.='</table>';
        return $output;
    }

}
