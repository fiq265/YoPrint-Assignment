<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileUploadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'filename'              => $this->original_filename,
            'status'                => ucwords($this->status),
            'total_rows'            => $this->total_rows,
            'processed_rows'        => $this->processed_rows,
            'progress_percentage'   => $this->progress_percentage,
            'error_message'         => $this->error_message,
            'uploaded_at'           => $this->created_at?->format('Y-m-d H:i:s'),
            'started_at'            => $this->started_at?->format('Y-m-d H:i:s'),
            'completed_at'          => $this->completed_at?->format('Y-m-d H:i:s'),
        ];
    }
}
