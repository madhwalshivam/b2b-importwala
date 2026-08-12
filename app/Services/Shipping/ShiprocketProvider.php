<?php
namespace App\Services\Shipping;

class ShiprocketProvider extends \Lib\Shipping\ShiprocketProvider implements ShippingProviderInterface {
    public function getTrackingStatus(string $awbCode): array {
        return parent::trackShipment($awbCode);
    }
}
