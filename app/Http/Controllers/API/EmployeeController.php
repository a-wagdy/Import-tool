<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Services\ImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use Throwable;

class EmployeeController extends APIController
{
    public function __construct(private readonly ImportService $importService)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function list(): AnonymousResourceCollection
    {
        return EmployeeResource::collection(Employee::query()->paginate(25));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function import(Request $request): JsonResponse
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv|max:2048',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return $this->responseWithError(400, $validator->errors()->first());
        }

        // Get validated data
        $file = $validator->validated()['file'];

        // Insert CSV data into the database.
        $this->importService->processCsvFile($file);

        return response()->json(['message' => 'CSV data imported successfully'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return EmployeeResource|JsonResponse
     */
    public function show(string $id): EmployeeResource|JsonResponse
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
    public function destroy(string $id): JsonResponse
    {
        try {
            if (!$employee = Employee::query()->find((int) $id)) {
                return $this->responseWithError(404, 'Employee not found');
            }

            $employee->delete();

            return response()->json([], 204);

        } catch (Throwable $exception) {
            return $this->responseWithError(500, 'Something went wrong. Please, try again');
        }
    }
}
