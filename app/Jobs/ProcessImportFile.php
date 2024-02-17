<?php

declare(strict_types=1);

namespace App\Jobs;

use Log;
use Throwable;
use Illuminate\Bus\Queueable;
use App\Services\Import\ImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportFile implements ShouldQueue
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
        try {
            $this->importService->processFile($this->filePath);
        } catch (Throwable $e) {
            Log::error('Error while importing the file: ' . $e->getMessage());
        }
    }
}
