<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Address;
use App\Models\Employee;
use App\Services\Import\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Log;
use Throwable;

class PostImportProcess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ImportService $importService;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly string $filePath)
    {
        $this->importService = new ImportService();
    }

    /**
     * Execute the job.
     * @throws Throwable
     */
    public function handle(): void
    {
        // Delete the temp file
        if (\is_file($this->filePath)) {
            File::delete($this->filePath);
        }

        try {
            Employee::whereNull('username')->orWhereNull('email')->delete();
            Address::whereNull('city')->orWhereNull('country')->delete();
        } catch (Throwable $e) {
            Log::error('Error while performing the post process on the imported file: ' . $e->getMessage());
        }
    }
}
