<?php

namespace Database\Factories;

use App\Modules\Commerce\Models\Order;
use App\Modules\Commerce\Models\Shipment;
use App\Shared\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shipment>
 */
class ShipmentFactory extends Factory
{
    protected $model = Shipment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'status' => ShipmentStatus::Pending,
            'recipient_name' => fake('ar_EG')->name(),
            'phone' => '01012345678',
            'alternate_phone' => null,
            'governorate' => 'القاهرة',
            'city' => 'مدينة نصر',
            'address_line1' => 'شارع النصر',
            'address_line2' => null,
            'landmark' => null,
            'shipping_fee_amount' => 0,
            'currency' => 'EGP',
            'carrier_name' => null,
            'carrier_reference' => null,
            'prepared_at' => null,
            'handed_to_carrier_at' => null,
            'in_transit_at' => null,
            'delivered_at' => null,
            'returned_at' => null,
            'canceled_at' => null,
            'meta' => null,
        ];
    }
}
