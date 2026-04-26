<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\District;
use App\Models\Province;
use App\Models\SubDistrict;
use App\Models\Village;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class IndonesiaRegionSeeder extends Command
{
    protected $signature = 'indonesia:seed {--force : Force seeding without confirmation}';

    protected $description = 'Seed Indonesia region data from CSV files';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will truncate and seed all region tables. Continue?')) {
            return self::FAILURE;
        }

        $this->seedCountries();
        $this->seedProvinces();
        $this->seedDistricts();
        $this->seedSubDistricts();
        $this->seedVillages();

        $this->newLine();
        $this->info('Indonesia region seeding completed!');
        $this->table(['Table', 'Count'], [
            ['Countries', Country::count()],
            ['Provinces', Province::count()],
            ['Districts', District::count()],
            ['Sub Districts', SubDistrict::count()],
            ['Villages', Village::count()],
        ]);

        return self::SUCCESS;
    }

    protected function seedCountries(): void
    {
        $this->info('Seeding countries...');

        Country::truncate();

        Country::firstOrCreate(['code' => 'ID'], ['name' => 'Indonesia']);

        $this->info('Countries seeded: '.Country::count());
    }

    protected function seedProvinces(): void
    {
        $this->info('Seeding provinces...');

        $country = Country::where('code', 'ID')->first();

        $csvFile = database_path('seeders/indonesia_provinces.csv');

        if (! File::exists($csvFile)) {
            $this->error("File not found: $csvFile");

            return;
        }

        Province::truncate();

        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle);

        $bar = $this->output->createProgressBar($this->countLines($csvFile) - 1);
        $bar->start();

        while (($row = fgetcsv($handle)) !== false) {
            $name = trim($row[1]);

            Province::firstOrCreate(
                ['country_id' => $country->id, 'name' => $name],
                ['country_id' => $country->id, 'name' => $name]
            );

            $bar->advance();
        }

        fclose($handle);
        $bar->finish();
        $this->newLine();
        $this->info('Provinces seeded: '.Province::count());
    }

    protected function seedDistricts(): void
    {
        $this->info('Seeding districts...');

        $csvFile = database_path('seeders/indonesia_districts.csv');

        if (! File::exists($csvFile)) {
            $this->error("File not found: $csvFile");

            return;
        }

        District::truncate();

        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle);

        $bar = $this->output->createProgressBar($this->countLines($csvFile) - 1);
        $bar->start();

        while (($row = fgetcsv($handle)) !== false) {
            $provinceName = trim($row[0]);
            $name = trim($row[1]);

            $province = Province::where('name', $provinceName)->first();

            if ($province) {
                District::firstOrCreate(
                    ['province_id' => $province->id, 'name' => $name],
                    ['province_id' => $province->id, 'name' => $name]
                );
            }

            $bar->advance();
        }

        fclose($handle);
        $bar->finish();
        $this->newLine();
        $this->info('Districts seeded: '.District::count());
    }

    protected function seedSubDistricts(): void
    {
        $this->info('Seeding sub-districts...');

        $csvFile = database_path('seeders/indonesia_sub_districts.csv');

        if (! File::exists($csvFile)) {
            $this->error("File not found: $csvFile");

            return;
        }

        SubDistrict::truncate();

        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle);

        $bar = $this->output->createProgressBar($this->countLines($csvFile) - 1);
        $bar->start();

        while (($row = fgetcsv($handle)) !== false) {
            $districtName = trim($row[0]);
            $name = trim($row[1]);

            $district = District::where('name', $districtName)->first();

            if ($district) {
                SubDistrict::firstOrCreate(
                    ['district_id' => $district->id, 'name' => $name],
                    ['district_id' => $district->id, 'name' => $name]
                );
            }

            $bar->advance();
        }

        fclose($handle);
        $bar->finish();
        $this->newLine();
        $this->info('Sub-districts seeded: '.SubDistrict::count());
    }

    protected function seedVillages(): void
    {
        $this->info('Seeding villages...');

        $csvFiles = glob(database_path('seeders/indonesia_villages_part_*.csv'));

        if (empty($csvFiles)) {
            $this->error('No village CSV files found');

            return;
        }

        Village::truncate();

        sort($csvFiles);

        $totalFiles = count($csvFiles);

        foreach ($csvFiles as $index => $csvFile) {
            $this->info('Processing file '.($index + 1)." of $totalFiles: ".basename($csvFile));

            $handle = fopen($csvFile, 'r');
            $header = fgetcsv($handle);

            $bar = $this->output->createProgressBar($this->countLines($csvFile) - 1);
            $bar->start();

            while (($row = fgetcsv($handle)) !== false) {
                $subDistrictName = trim($row[0]);
                $name = trim($row[1]);
                $postalCode = isset($row[2]) ? trim($row[2]) : null;

                $subDistrict = SubDistrict::where('name', $subDistrictName)->first();

                if ($subDistrict) {
                    Village::firstOrCreate(
                        ['sub_district_id' => $subDistrict->id, 'name' => $name],
                        [
                            'sub_district_id' => $subDistrict->id,
                            'name' => $name,
                            'postal_code' => $postalCode ?: null,
                        ]
                    );
                }

                $bar->advance();
            }

            fclose($handle);
            $bar->finish();
            $this->newLine();
        }

        $this->info('Villages seeded: '.Village::count());
    }

    protected function countLines(string $file): int
    {
        $lineCount = 0;
        $handle = fopen($file, 'r');
        while (! feof($handle)) {
            $line = fgets($handle);
            if ($line !== false) {
                $lineCount++;
            }
        }
        fclose($handle);

        return $lineCount;
    }
}
