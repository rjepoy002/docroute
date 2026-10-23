<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyDocRouteDepartmentsSeeder extends Seeder
{
    public const DEPARTMENTS = [
        'ACOD' => 'Area Central Operations Department',
        'ANOD' => 'Area North Operations Department',
        'ASOD' => 'Area South Operations Department',
        'CPD' => 'Corporate Planning Department',
        'FSD' => 'Finance Services Department',
        'ISD' => 'Institutional Services Department',
        'IAD' => 'Internal Audit Department',
        'OGM' => 'Office of the General Manager',
        'TSD' => 'Technical Services Department',
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $legacy = Department::where('code', 'ICT')->lockForUpdate()->first();
            if ($legacy) {
                // ICT is a division of CPD. Keep the row ID and all its references.
                if (Department::where('code', 'CPD')->exists()) {
                    throw new \RuntimeException('Both ICT and CPD exist. Reconcile their assignments before seeding; no records were changed.');
                }
                $legacy->update(['code' => 'CPD', 'name' => self::DEPARTMENTS['CPD'], 'is_active' => true]);
            }

            foreach (self::DEPARTMENTS as $code => $name) {
                Department::updateOrCreate(['code' => $code], ['name' => $name, 'is_active' => true]);
            }
        }, 3);
    }
}
