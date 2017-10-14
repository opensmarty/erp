<?php
/**
 * AbstrackShipment.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/9/22
 */

namespace renk\yiipal\shipment\providers;


use app\models\order\Order;

abstract class AbstrackShipment {

    protected function getBaseFilePath(){
        return \Yii::$app->basePath.'/web/shipment/static/';
    }

    public function create(Order $order){
    }

    protected function getRegionShortName($region){
        $region = strtolower($region);
        $regionMap = [
            "alabama"=>"AL",
            "alaska"=>"AK",
            "arizona"=>"AZ",
            "arkansas"=>"AR",
            "california"=>"CA",
            "colorado"=>"CO",
            "connecticut"=>"CT",
            "delaware"=>"DE",
            "florida"=>"FL",
            "georgia"=>"GA",
            "hawaii"=>"HI",
            "idaho"=>"ID",
            "illinois"=>"IL",
            "indiana"=>"IN",
            "iowa"=>"IA",
            "kansas"=>"KS",
            "kentucky"=>"KY",
            "louisiana"=>"LA",
            "maine"=>"ME",
            "maryland"=>"MD",
            "massachusetts"=>"MA",
            "michigan"=>"MI",
            "minnesota"=>"MN",
            "mississippi"=>"MS",
            "missouri"=>"MO",
            "montana"=>"MT",
            "nebraska"=>"NE",
            "nevada"=>"NV",
            "new hampshire"=>"NH",
            "new jersey"=>"NJ",
            "new mexico"=>"NM",
            "new york"=>"NY",
            "north carolina"=>"NC",
            "north dakota"=>"ND",
            "ohio"=>"OH",
            "oklahoma"=>"OK",
            "oregon"=>"OR",
            "pennsylvania"=>"PA",
            "rhode island"=>"RL",
            "south carolina"=>"SC",
            "south dakota"=>"SD",
            "tennessee"=>"TN",
            "texas"=>"TX",
            "utah"=>"UT",
            "vermont"=>"VT",
            "virginia"=>"VA",
            "washington"=>"WA",
            "west virginia"=>"WV",
            "wisconsin"=>"WI",
            "wyoming"=>"WY"
        ];
        if(isset($regionMap[$region])){
            return $regionMap[$region];
        }else{
            return $region;
        }
    }
}