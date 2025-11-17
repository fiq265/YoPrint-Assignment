<?php

namespace App\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use App\Imports\ProductImport;
use App\Models\FileUpload;

class FileUploadJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public FileUpload $fileUpload)
    {
        //
    }

    public function handle(): void
    {
        try {
            $this->fileUpload->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            $filePath = Storage::path($this->fileUpload->filename);
            
            if (!file_exists($filePath)) {
                throw new \Exception("File not found: {$filePath}");
            }

            $import = new ProductImport($this->fileUpload);

            Excel::import($import, $filePath);

            $this->fileUpload->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error('CSV Processing Error: ' . $e->getMessage(), [
                'file_upload_id' => $this->fileUpload->id,
                'exception' => $e,
            ]);

            $this->fileUpload->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
        }
    }
}
