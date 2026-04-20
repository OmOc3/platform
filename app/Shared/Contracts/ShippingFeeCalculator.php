<?php

namespace App\Shared\Contracts;

use App\Modules\Students\Models\Student;
use Illuminate\Support\Collection;

interface ShippingFeeCalculator
{
    /**
     * @param  Collection<int, mixed>  $items
     * @return array{
     *     amount:int,
     *     label:string,
     *     warning:?string,
     *     can_deliver:bool,
     *     supported_governorates:Collection<int, string>
     * }
     */
    public function calculate(Student $student, Collection $items, array $address = []): array;
}
