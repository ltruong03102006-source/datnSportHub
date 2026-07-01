<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VietnamUnitsSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/vn_units.json');

        if (! is_file($path)) {
            $this->command?->warn('vn_units.json not found, skipping.');

            return;
        }

        $data = json_decode(file_get_contents($path), true);

        $provinces = [];
        $wards = [];

        foreach ($data as $province) {
            $provinces[] = ['code' => $province['Code'], 'name' => $province['FullName']];

            foreach ($province['Wards'] ?? [] as $ward) {
                $wards[] = [
                    'code' => $ward['Code'],
                    'province_code' => $ward['ProvinceCode'],
                    'name' => $ward['FullName'],
                ];
            }
        }

        DB::table('provinces')->upsert($provinces, ['code'], ['name']);

        foreach (array_chunk($wards, 500) as $chunk) {
            DB::table('wards')->upsert($chunk, ['code'], ['province_code', 'name']);
        }

        $this->command?->info('Seeded ' . count($provinces) . ' provinces and ' . count($wards) . ' wards.');
    }
}
