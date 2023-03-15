<?php

namespace App\Http\Controllers\API;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Resources\EmployeeResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class EmployeeController extends APIController
{
    /**
     * Display a listing of the resource.
     */
    public function list(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return EmployeeResource::collection(Employee::query()->paginate(15));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function import(Request $request)
    {
        // https://techvblogs.com/blog/importing-large-csv-files-in-mysql-using-laravel
        // https://laravel.com/docs/10.x/collections#lazy-collections

        //Storage::disk('local')->put('test.csv', $request->getContent());


//        File::put(time().'data_binary.csv', $request->getContent());
//        dd(11);


        LazyCollection::make(function () {

            $handle = fopen(public_path('1678874255data_binary.csv'), 'r');

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

//                    if (!\is_numeric($row[0])) {
//                        logger('not int');
//                        continue;
//                    }

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
        logger('done');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!$employee = Employee::query()->find((int) $id)) {
            return $this->responseWithError(404, 'Employee not found');
        }
        return new EmployeeResource($employee);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
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
