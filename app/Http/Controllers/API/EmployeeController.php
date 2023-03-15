<?php

namespace App\Http\Controllers\API;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Services\ImportService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\EmployeeResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmployeeController extends APIController
{
    private ImportService $importService;

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
    }

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
        // Make sure the uploaded file in csv
        if ($request->header('Content-Type') !== 'text/csv') {
            return $this->responseWithError(400, 'Invalid file type');
        }

        // Read the raw CSV data from the input stream
        $input_stream = fopen('php://input', 'r');
        if ($input_stream === false) {
            return $this->responseWithError(500, 'Failed to read input stream');
        }

        // Create temp file from input stream
        $temp = $this->importService->createTempFileFromInput($input_stream);

        // Insert CSV data into the database.
        $this->importService->processCsvData($temp);

        return response()->json(['message' => 'CSV data imported successfully'], 200);
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
