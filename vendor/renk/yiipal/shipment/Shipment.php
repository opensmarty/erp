<?php
/**
 * Shipment.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/9/22
 */

namespace renk\yiipal\shipment;


class Shipment {
    /**
     * @var string
     */
    protected static $carriersNamespace = "renk\\yiipal\\shipment\\providers";

    /**
     * Get the shipment for the given carrier name.
     *
     * @param string $carrier
     *
     * @return AbstractTracker
     * @throws \Exception
     */
    public static function get($carrier)
    {
        $carrier = ucfirst(strtolower($carrier)).'Shipment';
        if (!static::isValidCarrier($carrier)) {
            throw new \Exception("Unknown carrier [{$carrier}]");
        }

        $className = self::$carriersNamespace . '\\' . $carrier;
        $shipment = new $className();
        return $shipment;
    }

    /**
     * Check if a shipment exists for the given carrier.
     *
     * @param string $carrier
     *
     * @return bool
     */
    protected static function isValidCarrier($carrier)
    {
        return class_exists(self::$carriersNamespace . '\\' . $carrier);
    }
}