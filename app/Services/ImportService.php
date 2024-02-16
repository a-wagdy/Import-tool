<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;

class ImportService
{
    /**
     * Insert CSV data into the database.
     *
     * @param $temp
     * @return void
     */
    public function processCsvData($temp): void
    {
        LazyCollection::make(function () use ($temp) {
            // Open the temporary file for reading
            $file = fopen(stream_get_meta_data($temp)['uri'], 'r');

            while (($row = fgetcsv($file)) !== false) {
                yield $row;
            }

            fclose($file);
            fclose($temp);
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
                    } catch (\Throwable $e) {
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
            'employee_old_id' => $row[0],
            'name_prefix' => $row[1],
            'first_name' => $row[2],
            'middle_initial' => $row[3],
            'last_name' => $row[4],
            'gender' => Employee::setGenderAsInteger($row[5]),
            'email' => $row[6],
            'date_of_birth' => Employee::setDateAsValidDateTime($row[7]),
            'time_of_birth' => Employee::setDateAsValidDateTime($row[8]),
            'age' => $row[9],
            'date_of_joining' => Employee::setDateAsValidDateTime($row[10]),
            'age_in_company' => $row[11],
            'phone_number' => Employee::setPhoneNumber($row[12]),
            'username' => $row[18]
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
            'place_name' => $row[13],
            'country' => $row[14],
            'city' => $row[15],
            'zip' => $row[16],
            'region' => $row[17],
        ];
    }

    /**
     * Prepare address data.
     *
     * @param $input_stream
     * @return false|resource
     */
    public function createTempFileFromInput($input_stream)
    {
        // Create a temporary file
        $temp = tmpfile();

        // Write the CSV data to the temporary file
        while (!feof($input_stream)) {
            fwrite($temp, fread($input_stream, 8192));
        }

        fclose($input_stream);

        return $temp;
    }
}
