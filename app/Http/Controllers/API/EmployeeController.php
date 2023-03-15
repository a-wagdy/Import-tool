<?php

namespace App\Http\Controllers\API;

use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\LazyCollection;
use App\Http\Resources\EmployeeResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmployeeController extends APIController
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function list(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return EmployeeResource::collection(Employee::query()->paginate(25));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @see https://laravel.com/docs/10.x/collections#lazy-collections
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): \Illuminate\Http\JsonResponse
    {
        if ($request->header('Content-Type') !== 'text/csv') {
            return $this->responseWithError(400, 'Invalid file type');
        }

        // Create a temporary file
        $temp = tmpfile();

        // Write the CSV data to the temporary file
        fwrite($temp, $request->getContent());

        // Load the content in a memory-safe state
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
            'message' => 'CSV data imported successfully'
        ], 200);
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
