<?php
/**
 * SendMsg.php
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/10/12
 */

namespace renk\yiipal\alidayu;

class SendMsg {
    public function sendVoiceMsg(){
        require_once("TopSdk.php");
        $this->send('13572543260');
        $this->send('18905945939');
        $this->send('18905942769');
        $this->send('17792318530');
        $this->send('13720586732');
    }

    public function send($number){
        $c = new \TopClient;
        $c->appkey = '23457613';
        $c->secretKey = 'df955bf8be2959fb4f9ba03cd0508d1e';
        $req = new \AlibabaAliqinFcTtsNumSinglecallRequest;
        $req->setCalledNum($number);
        $req->setCalledShowNum("051482043270");
        $req->setTtsCode("TTS_14935410");
        $req->setTtsParam('{"name":"管理员","website":"Jeulia","time":"一个小时"}');
        $req->setExtend("123");
        $resp = $c->execute($req);
        print_r($resp);
    }
}