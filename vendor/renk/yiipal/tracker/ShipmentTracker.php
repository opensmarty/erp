<?php
/**
 * shipment.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/7/1
 */
namespace renk\yiipal\tracker;

class ShipmentTracker {
    /**
     * @var string
     */
    protected static $carriersNamespace = "renk\\yiipal\\tracker\\providers";

    /**
     * Get the tracker for the given carrier name.
     *
     * @param string                $carrier
     *
     * @return AbstractTracker
     * @throws \Exception
     */
    public static function get($carrier)
    {
        if (!static::isValidCarrier($carrier)) {
            throw new \Exception("Unknown carrier [{$carrier}]");
        }

        $className = self::$carriersNamespace . '\\' . $carrier;
        $tracker = new $className();
        return $tracker;
    }

    /**
     * Check if a tracker exists for the given carrier.
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