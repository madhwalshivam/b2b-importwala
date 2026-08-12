<?php
namespace Lib\Shipping;

interface ShippingProviderInterface {
    /**
     * Push order details to shipping provider and generate shipment
     *
     * @param array $orderDetails Order info, items, customer shipping address, weight
     * @return array Contains 'success', 'shipment_id', 'awb_code', 'courier_name', 'tracking_url', etc.
     */
    public function createShipment(array $orderDetails): array;

    /**
     * Retrieve tracking status for an AWB code or order ID
     *
     * @param string $awbCode
     * @return array Contains 'status', 'current_location', 'history', etc.
     */
    public function trackShipment(string $awbCode): array;

    /**
     * Cancel an existing shipment
     *
     * @param string $shipmentId or order ID
     * @return array
     */
    public function cancelShipment(string $shipmentId): array;

    /**
     * Fetch available pickup locations from provider
     *
     * @return array
     */
    public function getPickupLocations(): array;

    /**
     * Test connection to shipping provider API
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function testConnection(): array;
}
