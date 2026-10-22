<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TrackingNumberAllocator
{
    /** Reserve BEFORE starting the document transaction; failed sends consume their reservation. */
    public function reserve(): array
    {
        $year = (int) now('Asia/Manila')->format('Y');
        $id = DB::table('document_numbers')->insertGetId(['year' => $year, 'allocated_at' => now()]);

        return ['document_number_id' => $id, 'tracking_number' => sprintf('DR-%d-%06d', $year, $id)];
    }
}
