<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    protected $fillable = [
        'filename',
        'original_filename',
        'status',
        'total_rows',
        'processed_rows',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_rows === 0 || $this->total_rows === null) {
            return 0;
        }
        return (int) round(($this->processed_rows / $this->total_rows) * 100);
    }
}
