<?php

namespace renk\yiipal\tracker\providers;

use renk\yiipal\tracker\providers\AbstractTracker;

class ARAMEX extends AbstractTracker
{
    protected function sendRequest($number){
        $soapClient = new \SoapClient(dirname(__FILE__).'/data/Aramex_tracking.wsdl');
        $params = array(
            'ClientInfo'  			=> array(
                'AccountCountryCode'	=> 'JO',
                'AccountEntity'		 	=> 'CAN',
                'AccountNumber'		 	=> '200330',
                'AccountPin'		 	=> '321321',
                'UserName'			 	=> 'renkuan@jeulia.net',
                'Password'			 	=> 'Jeulia2016Abcd',
                'Version'			 	=> 'v1.0'
            ),

            'Transaction' 			=> array(
                'Reference1'			=> '001'
            ),
            'Shipments'				=> array(
                $number
            )
        );

        // calling the method and printing results
        try {
            $response = $soapClient->TrackShipments($params);
        } catch (SoapFault $fault) {
            $response = false;
        }
        return $response;
    }

    protected function checkDelivered($response){
    }

    public function getInfoTable($number){
        $response = $this->sendRequest($number);
        if($response) {
            if (!isset($response->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult)) {
                $output = '<table class="table table-striped table-bordered">';
                $output .= '<tr><td>没有物流信息</td></tr>';
                $output .= '</table>';
                return $output;
            }

            $list = $response->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult;
            $output = '<table class="table table-striped table-bordered">';
            $output .= '<tr><th>Location</th><th>Date</th><th class="full">Description</th></tr>';
            if(is_array($list)){
                foreach ($list as $row) {

                    $output .= '<tr><td>' . $row->UpdateLocation . '</td><td>' . $row->UpdateDateTime . '</td><td>' . $row->UpdateDescription . '</td></tr>';

                }
            }else{
                $output .= '<tr><td>' . $list->UpdateLocation . '</td><td>' . $list->UpdateDateTime . '</td><td>' . $list->UpdateDescription . '</td></tr>';
            }

            $output .= '</table>';
            return $output;
        }else{
            $output = '<table class="table table-striped table-bordered">';
            $output .= '<tr><td>没有物流信息</td></tr>';
            $output .= '</table>';
            return $output;
        }
    }
}
