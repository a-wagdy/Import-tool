<?php

namespace App\Http\Controllers\API;

use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\LazyCollection;
use App\Http\Resources\EmployeeResource;

class EmployeeController extends APIController
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function list(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return EmployeeResource::collection(Employee::query()->paginate(15));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): \Illuminate\Http\JsonResponse
    {
        // Copy the CURL content in a csv file.
        File::put('data_binary.csv', $request->getContent());

        // Load the content in a memory-safe state
        LazyCollection::make(function () {

            $handle = fopen(public_path('data_binary.csv'), 'r');

            while (($line = fgetcsv($handle, 4096)) !== false) {
                yield $line;
            }

            fclose($handle);
        })
            ->skip(1)
            ->chunk(300)
            ->each(function (LazyCollection $chunk) {

                $employees = $addresses = [];
                foreach ($chunk as $index => $row) {

                    $employees[] = [
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
                    $addresses[] = [
                        'employee_id' => $index,
                        'place_name' => $row[13],
                        'country' => $row[14],
                        'city' => $row[15],
                        'zip' => $row[16],
                        'region' => $row[17],
                    ];
                }
                DB::table('employees')->insert($employees);
                DB::table('addresses')->insert($addresses);
            });
        return response()->json([
            'message' => 'Imported successfully.'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return EmployeeResource|JsonResponse
     */
    public function show(string $id): EmployeeResource|\Illuminate\Http\JsonResponse
    {
        if (!$employee = Employee::query()->find((int) $id)) {
            return $this->responseWithError(404, 'Employee not found');
        }
        return new EmployeeResource($employee);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            if (!$employee = Employee::query()->find((int) $id)) {
                return $this->responseWithError(404, 'Employee not found');
            }
            $employee->delete();

            return response()->json([], 204);

        } catch (\Throwable $exception) {
            return $this->responseWithError(500, 'Something went wrong. Please, try again');
        }
    }
}
