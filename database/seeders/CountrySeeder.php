<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\District;
use App\Models\Province;
use App\Models\SubDistrict;
use App\Models\Village;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $sqlFile = storage_path('app/wilayah.sql');

        if (! file_exists($sqlFile)) {
            $this->command->info('Downloading wilayah.sql...');
            $url = 'https://raw.githubusercontent.com/cahyadsn/wilayah/master/db/wilayah.sql';
            $content = Http::timeout(300)->get($url)->body();
            File::put($sqlFile, $content);
            $this->command->info('Downloaded to: '.$sqlFile);
        } else {
            $content = file_get_contents($sqlFile);
        }

        $this->command->info('Processing wilayah data...');

        preg_match_all("/\\('([^']+)','([^']+)'\\)/", $content, $matches);

        $totalRecords = count($matches[0]);
        $this->command->info("Found $totalRecords records");

        $kodeMap = [];

        foreach ($matches[0] as $index => $fullMatch) {
            $kode = $matches[1][$index];
            $nama = trim($matches[2][$index]);
            $kodeParts = explode('.', $kode);

            // Determine level based on number of dots
            $dotCount = substr_count($kode, '.');

            if ($dotCount == 0) {
                // Country (2 digits like "11")
                $id = Country::firstOrCreate(['code' => $kode], ['name' => $nama])->id;
                $kodeMap[$kode] = $id;
            } elseif ($dotCount == 1) {
                // Province (4 digits like "11.01")
                $parent = $kodeParts[0];
                $parentId = $kodeMap[$parent] ?? null;
                if ($parentId) {
                    $id = Province::firstOrCreate(
                        ['country_id' => $parentId, 'name' => $nama],
                        ['country_id' => $parentId, 'name' => $nama, 'code' => $kode]
                    )->id;
                    $kodeMap[$kode] = $id;
                }
            } elseif ($dotCount == 2) {
                // District + auto subdistrict (6 digits like "11.01.01")
                $parent = $kodeParts[0].'.'.$kodeParts[1];
                $parentId = $kodeMap[$parent] ?? null;
                if ($parentId) {
                    $district = District::firstOrCreate(
                        ['province_id' => $parentId, 'name' => $nama],
                        ['province_id' => $parentId, 'name' => $nama, 'code' => $kode]
                    );
                    $districtId = $district->id;

                    // Auto-create subdistrict to hold villages
                    $subDistrict = SubDistrict::firstOrCreate(
                        ['district_id' => $districtId, 'name' => $nama],
                        ['district_id' => $districtId, 'name' => $nama, 'code' => $kode.'.00']
                    );

                    // Store by full code for villages to find
                    $kodeMap[$kode] = $subDistrict->id;
                }
            } else {
                // Village (7+ dots like "11.01.01.2001")
                $parent = $kodeParts[0].'.'.$kodeParts[1].'.'.$kodeParts[2];
                $subDistrictId = $kodeMap[$parent] ?? null;

                if ($subDistrictId) {
                    $villageName = $nama;
                    $postalCode = null;

                    if (preg_match('/^(.+?)(\d{5})$/', $nama, $vm)) {
                        $villageName = trim($vm[1]);
                        $postalCode = $vm[2];
                    }

                    Village::firstOrCreate(
                        ['sub_district_id' => $subDistrictId, 'name' => $villageName],
                        [
                            'sub_district_id' => $subDistrictId,
                            'name' => $villageName,
                            'postal_code' => $postalCode,
                            'code' => $kode,
                        ]
                    );
                }
            }

            if ($index > 0 && $index % 10000 === 0) {
                $this->command->info("Processed $index / $totalRecords records...");
            }
        }

        $this->command->info('Country seeder completed!');
        $this->command->info('Countries: '.Country::count());
        $this->command->info('Provinces: '.Province::count());
        $this->command->info('Districts: '.District::count());
        $this->command->info('Sub Districts: '.SubDistrict::count());
        $this->command->info('Villages: '.Village::count());
    }
}
