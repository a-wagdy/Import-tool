<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Resources\EmployeeResource;
use App\Jobs\PostImportProcess;
use App\Jobs\ProcessImportFile;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use Log;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;
use Illuminate\Support\Facades\Bus;

class EmployeeController extends APIController
{
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

        /** @var UploadedFile $file */
        $file = $validator->validated()['file'];

        $file = $file->move(storage_path('/imports'), time() . '.csv');

        if (!$filePath = $file->getRealPath()) {
            return $this->responseWithError(400, 'Could not move the file');
        }

        // Dispatch jobs in order
        Bus::chain([
            new ProcessImportFile($filePath),
            new PostImportProcess($filePath),
        ])->dispatch();

        return response()->json(['message' => 'Importing the CSV file...'], 200);
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

            $employee->addresses()->delete();
            $employee->delete();

            return response()->json(['Deleted successfully'], 204);

        } catch (Throwable $exception) {
            Log::error('Error deleting a record: ' . $exception->getMessage());
            return $this->responseWithError(500, 'Something went wrong. Please, try again');
        }
    }
}
