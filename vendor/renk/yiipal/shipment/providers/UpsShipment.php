<?php
/**
 * UpsShipment.php
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/9/22
 */

namespace renk\yiipal\shipment\providers;
require_once __DIR__ . '/libs/ups/autoload.php';
use app\helpers\CommonHelper;
use app\models\order\Order;
use app\models\shipment\Shipment;
use Ups;
use yii;
use yii\imagine\Image;

class UpsShipment extends AbstrackShipment{

    public function create(Order $order){
        $shipmentInfo = $this->prepare($order);
        if(!$shipmentInfo['accept']) return false;
        $order->shipping_track_no = $shipmentInfo['accept']->ShipmentIdentificationNumber;
        $order->has_shipment = 1;
        $order->save();

        $shipment = Shipment::find()->where(['order_id'=>$order->id])->one();
        if(empty($shipment)){
            $shipment = new Shipment();
            $shipment->order_id = $order->id;
            $shipment->uid = \Yii::$app->user->id;
            $shipment->shipping_label = $order->shipping_description;
        }
        $shipment->shipping_method = $order->shipping_method;
        $shipment->shipping_number = $order->shipping_track_no;
        $shipment->save();

        $this->generateLabelHtml($shipmentInfo);
        return $order->shipping_track_no;
    }

    public function generateLabelHtml($shipmentInfo){
        if(!$shipmentInfo['accept']) return false;
        $trackNo = $shipmentInfo['accept']->ShipmentIdentificationNumber;
        $path = $this->getBaseFilePath().'UPS/'.$trackNo;
        @mkdir($path,0777,true);
        file_put_contents($path."/label-html.html",base64_decode($shipmentInfo['accept']->PackageResults->LabelImage->HTMLImage));
        $imageName = $path."/label".$trackNo.".gif";
        file_put_contents($path."/label".$trackNo.".gif",base64_decode($shipmentInfo['accept']->PackageResults->LabelImage->GraphicImage));
        Image::crop($imageName,1200,800)->save($imageName);
        CommonHelper::addLogoToLabel(yii::$app->basePath.'/web/images/UPS-Jeulia-Logo.gif',
            $imageName,
            $imageName
        );
        file_put_contents($path."/invoice.pdf",base64_decode($shipmentInfo['accept']->Form->Image->GraphicImage));
    }

    public function voidShipment($order){
        $api = new Ups\Shipping('6D14658C72AC96EE', '1W508R-API', 'Aa123456');
        try{
            $output = $api->void($order->shipping_track_no);
        } catch (\Exception $e) {
            print_r($e);exit;
        }
        print_r($output);exit;
    }


    public function prepare($order){
        // Start shipment
        $shipment = new \Ups\Entity\Shipment;
        // Set shipper
        $shipper = $shipment->getShipper();
        $shipper->setShipperNumber('1W508R');
        $shipper->setName('GZ JEULIA JEWELLERY CO.,LTD');
        $shipper->setAttentionName('MR LIN');
        $shipperAddress = $shipper->getAddress();
        $shipperAddress->setAddressLine1('SHATOU STREET YINJIAN RD 123#');
        $shipperAddress->setPostalCode('511400');
        $shipperAddress->setCity('GUANGZHOU');
        $shipperAddress->setStateProvinceCode('GD');
        $shipperAddress->setCountryCode('CN');
        $shipper->setAddress($shipperAddress);
        $shipper->setEmailAddress('linxia@jeulia.net');
        $shipper->setPhoneNumber('18003152361');
        $shipment->setShipper($shipper);


        //Set ship to
        $shipTo = $this->buildShipTo($order->address);
        $shipment->setShipTo($shipTo);

        //Set sold to
        $soldTo = $this->buildSoldTo($order->address);
        $shipment->setSoldTo($soldTo);

        // Set service
        $service = new \Ups\Entity\Service;
        $service->setCode(\Ups\Entity\Service::S_SAVER);
        $service->setDescription($service->getName());
        $shipment->setService($service);

        // Set invoice
        $invoice = new \Ups\Entity\InvoiceLineTotal();
        //TODO:发票币种
        //$invoice->setCurrencyCode('RMB');
        //货物发票的申报价值
        $invoice->setMonetaryValue(30);
        $shipment->setInvoiceLineTotal($invoice);

        $shipmentServiceOptions = new \Ups\Entity\ShipmentServiceOptions();
        // 通知信息
        $notification = new \Ups\Entity\Notification();
        $notification->setNotificationCode(7);
        $emailMessage = new \Ups\Entity\EmailMessage();
        $emailMessage->setEmailAddresses(['linxia@jeulia.net','renkuan@jeulia.net']);
        $notification->setEmailMessage($emailMessage);
        $shipmentServiceOptions->addNotification($notification);

        // 发票信息
        $internationalForms = new \Ups\Entity\InternationalForms();
        $internationalForms->setType('01');
        $product = new \Ups\Entity\Product();
        $product->setDescription1("ALLOY RING");
        $unit = new \Ups\Entity\Unit();
        $number = $this->getItemsTotal($order);
        $unit->setNumber($number);
        $unit->setValue(30);
        $unitOfMeasurement = new \Ups\Entity\UnitOfMeasurement();
        $unitOfMeasurement->setCode("PCS");
        $unitOfMeasurement->setDescription("PCS");
        $unit->setUnitOfMeasurement($unitOfMeasurement);
        $product->setUnit($unit);
        $product->setOriginCountryCode('CN');
        $internationalForms->addProduct($product);
        $internationalForms->setInvoiceDate(new \DateTime());
        $internationalForms->setCurrencyCode("USD");
        $internationalForms->setReasonForExport('SALE');
        $shipmentServiceOptions->setInternationalForms($internationalForms);
        $shipment->setShipmentServiceOptions($shipmentServiceOptions);

        // Set description
        $shipment->setDescription('ALLOY RING');

        // Add Package
        $package = new \Ups\Entity\Package();
        $package->getPackagingType()->setCode(\Ups\Entity\PackagingType::PT_PACKAGE);
        $package->getPackageWeight()->setWeight(0.5);
        $unit = new \Ups\Entity\UnitOfMeasurement;
        $unit->setCode(\Ups\Entity\UnitOfMeasurement::UOM_KGS);
        $package->getPackageWeight()->setUnitOfMeasurement($unit);
        $package->setDescription('ALLOY RING');
        // Add this package
        $shipment->addPackage($package);

        // Set Reference Number
        $referenceNumber = new \Ups\Entity\ReferenceNumber;
        $referenceNumber->setCode(\Ups\Entity\ReferenceNumber::CODE_PURCHASE_ORDER_NUMBER);
        //订单编号
        $referenceNumber->setValue($order->ext_order_id);
        $referenceNumber2 = new \Ups\Entity\ReferenceNumber;
//        $referenceNumber2->setCode(\Ups\Entity\ReferenceNumber::CODE_STORE_NUMBER);
//        $referenceNumber2->setValue('Jeulia.com');
        $shipment->setReferenceNumber($referenceNumber);
        $shipment->setReferenceNumber2($referenceNumber2);

        // Set payment information
        $shipment->setPaymentInformation(new \Ups\Entity\PaymentInformation('prepaid', (object)array('AccountNumber' => '1W508R')));

        // Ask for negotiated rates (optional)
        $rateInformation = new \Ups\Entity\RateInformation;
        $rateInformation->setNegotiatedRatesIndicator(1);
        $shipment->setRateInformation($rateInformation);


        // Get shipment info
        try {
            $output = ['confirm'=>false,'accept'=>false];
            $api = new Ups\Shipping('6D14658C72AC96EE', '1W508R-API', 'Aa123456',false);
            $confirm = $api->confirm(\Ups\Shipping::REQ_VALIDATE, $shipment);
            $output['confirm'] = $confirm;
            if ($confirm) {
                $accept = $api->accept($confirm->ShipmentDigest);
                $output['accept'] = $accept;
                return $output;
                echo $accept->ShipmentIdentificationNumber;
            }else{
                return $output;
            }
        } catch (\Exception $e) {
            print_r($e);exit;
        }
    }

    private function buildShipTo($data){
        $address = $this->getCustomerAddress($data);
        $shipTo = new \Ups\Entity\ShipTo();
        $shipTo->setAddress($address);
        $name = CommonHelper::filterEmptyStr($data->firstname).' '.CommonHelper::filterEmptyStr($data->lastname);
        $shipTo->setAttentionName($name);
        $shipTo->setCompanyName($shipTo->getAttentionName());
//        $shipTo->setEmailAddress($data->email);
        $shipTo->setPhoneNumber($data->telephone);
        return $shipTo;
    }

    private function buildSoldTo($data){
        $address = $this->getCustomerAddress($data);
        $soldTo = new \Ups\Entity\SoldTo;
        $soldTo->setAddress($address);
        $name = CommonHelper::filterEmptyStr($data->firstname).' '.CommonHelper::filterEmptyStr($data->lastname);
        $soldTo->setAttentionName($name);
        $soldTo->setCompanyName($soldTo->getAttentionName());
        $soldTo->setEmailAddress($data->email);
        $soldTo->setPhoneNumber($data->telephone);
        return $soldTo;
    }

    private function getCustomerAddress($data){
        $address = new \Ups\Entity\Address();
        $street = $data->street? str_replace(["\r\n", "\r", "\n"], ' ', $data->street) : '';
        $street = str_replace(",",' ',$street);
        $cityRegion = CommonHelper::filterEmptyStr($data->city).' '.CommonHelper::filterEmptyStr($data->region).' '.CommonHelper::filterEmptyStr($data->postcode);
        $cityRegion = str_replace(",",' ',$cityRegion);
        if(strlen($street)>35){
            $address->setAddressLine1(substr($street,0,30));
            $address->setAddressLine2(substr($street,30));
            $address->setAddressLine3($cityRegion);
        }else{
            $address->setAddressLine1($street);
            $address->setAddressLine2($cityRegion);
        }
        $data->postcode = preg_replace("/-.*/","",$data->postcode);
        $address->setPostalCode($data->postcode);
        $address->setStateProvinceCode(CommonHelper::filterEmptyStr($this->getRegionShortName($data->region)));
        $address->setCity($data->city);
        $address->setCountryCode($data->country_id);
        return $address;
    }

    private function getItemsTotal($order){
        $total = 0;
        foreach($order->items as $item){
            $total += $item->qty_ordered;
        }
        return $total;
    }
}