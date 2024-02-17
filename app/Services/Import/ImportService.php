<?php

declare(strict_types=1);

namespace App\Services\Import;

use Throwable;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class ImportService implements ImportInterface
{
    /**
     * Insert CSV data into the database.
     *
     * @see https://laravel.com/docs/10.x/collections#lazy-collections
     * @param string $filePath
     * @return void
     * @throws Throwable
     */
    public function processFile(string $filePath): void
    {
        if (!\is_file($filePath)) {
            throw new \Exception("Path {$filePath} is not a real path");
        }

        $fileStream = fopen($filePath, 'r');

        LazyCollection::make(function () use ($fileStream) {
            while (($row = fgetcsv($fileStream)) !== false) {
                yield $row;
            }

            fclose($fileStream);
        })
            ->skip(1)
            ->chunk(300)
            ->each(function (LazyCollection $chunk) {
                $employees = $addresses = [];
                foreach ($chunk as $index => $row) {
                    $employees[] = $this->prepareEmployeeData($index, $row);
                    $addresses[] = $this->prepareAddressData($index, $row);
                }

                // Use a database transaction to ensure atomicity of the inserts
                DB::transaction(function () use ($employees, $addresses) {
                    DB::table('employees')->insert($employees);
                    DB::table('addresses')->insert($addresses);
                });
            });
    }

    /**
     * Prepare employee data.
     *
     * @param int $index
     * @param array $row
     *
     * @return array
     */
    public function prepareEmployeeData(int $index, array $row): array
    {
        $mapped = $this->getFileMappedData();

        return [
            'id' => $index,
            'employee_old_id' => $row[$mapped['employee_old_id']],
            'name_prefix' => $row[$mapped['name_prefix']],
            'first_name' => $row[$mapped['first_name']],
            'middle_initial' => $row[$mapped['middle_initial']],
            'last_name' => $row[$mapped['last_name']],
            'gender' => Employee::setGenderAsInteger($row[$mapped['gender']]),
            'email' => $row[$mapped['email']],
            'date_of_birth' => Employee::setDateAsValidDateTime($row[$mapped['date_of_birth']]),
            'time_of_birth' => Employee::setDateAsValidDateTime($row[$mapped['time_of_birth']]),
            'age' => $row[$mapped['age']],
            'date_of_joining' => Employee::setDateAsValidDateTime($row[$mapped['date_of_joining']]),
            'age_in_company' => $row[$mapped['age_in_company']],
            'phone_number' => Employee::setPhoneNumber($row[$mapped['phone_number']]),
            'username' => $row[$mapped['username']],
        ];
    }

    /**
     * Prepare address data.
     *
     * @param int $index
     * @param array $row
     *
     * @return array
     */
    public function prepareAddressData(int $index, array $row): array
    {
        $mapped = $this->getFileMappedData();

        return [
            'employee_id' => $index,
            'place_name' => $row[$mapped['place_name']],
            'country' => $row[$mapped['country']],
            'city' => $row[$mapped['city']],
            'zip' => $row[$mapped['zip']],
            'region' => $row[$mapped['region']],
        ];
    }

    /**
     * Map the CSV file columns to its index number.
     *
     * @return array
     */
    public function getFileMappedData(): array
    {
        return [
            'employee_old_id' => 0,
            'name_prefix' => 1,
            'first_name' => 2,
            'middle_initial' => 3,
            'last_name' => 4,
            'gender' => 5,
            'email' => 6,
            'date_of_birth' => 7,
            'time_of_birth' => 8,
            'age' => 9,
            'date_of_joining' => 10,
            'age_in_company' => 11,
            'phone_number' => 12,
            'place_name' => 13,
            'country' => 14,
            'city' => 15,
            'zip' => 16,
            'region' => 17,
            'username' => 18,
        ];
    }
}
