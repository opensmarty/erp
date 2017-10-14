<?php

namespace renk\yiipal\tracker\providers;

abstract class AbstractTracker
{
    /**
     * Track the given number.
     *
     * @param string      $number
     * @param string|null $language
     * @param array       $params
     *
     * @return Track
     */
    public function track($number)
    {
        $output = array();
        $response = $this->sendRequest($number);
        if($response){
            $output['delivered'] = $this->checkDelivered($response);
        }else{
            $output['delivered'] = false;
        }
        return $output;
    }

    abstract protected function sendRequest($number);
    abstract protected function checkDelivered($response);

    public function url_post($url, $post){
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => http_build_query($post),
                'timeout' => 20
            )
        ));
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    public function getResult(){}
}
