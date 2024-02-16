<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
use Throwable;

class ImportService
{
    /**
     * Insert CSV data into the database.
     *
     * @see https://laravel.com/docs/10.x/collections#lazy-collections
     * @param UploadedFile $file
     * @return void
     * @throws Throwable
     */
    public function processCsvFile(UploadedFile $file): void
    {
        $splFileObject = $file->openFile();

        LazyCollection::make(function () use ($splFileObject) {
            while (($row = $splFileObject->fgetcsv()) !== false) {
                yield $row;
            }

            // Close the file handle
            $splFileObject = null;
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
                    try {
                        DB::table('employees')->insert($employees);
                        DB::table('addresses')->insert($addresses);
                    } catch (Throwable $e) {
                        Log::error('Error importing CSV data: ' . $e->getMessage());
                        throw $e;
                    }
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
        return [
            'id' => $index,
            'employee_old_id' => $row[0] ?? null,
            'name_prefix' => $row[1] ?? null,
            'first_name' => $row[2] ?? null,
            'middle_initial' => $row[3] ?? null,
            'last_name' => $row[4] ?? null,
            'gender' => isset($row[5]) ? Employee::setGenderAsInteger($row[5]) : null,
            'email' => isset($row[6]) ?? null,
            'date_of_birth' => isset($row[7]) ? Employee::setDateAsValidDateTime($row[7]) : null,
            'time_of_birth' => isset($row[8]) ? Employee::setDateAsValidDateTime($row[8]) : null,
            'age' => isset($row[9]) ?? null,
            'date_of_joining' => isset($row[10]) ? Employee::setDateAsValidDateTime($row[10]) : null,
            'age_in_company' => isset($row[11]) ?? null,
            'phone_number' => isset($row[12]) ? Employee::setPhoneNumber($row[12]) : null,
            'username' => $row[18] ?? null,
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
        return [
            'employee_id' => $index,
            'place_name' => $row[13] ?? null,
            'country' => $row[14] ?? null,
            'city' => $row[15] ?? null,
            'zip' => $row[16] ?? null,
            'region' => $row[17] ?? null,
        ];
    }
}
