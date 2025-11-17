<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv', 'max:70240'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required'     => 'Please select a CSV file to upload.',
            'file.mimes'        => 'The file must be a CSV file.',
            'file.max'          => 'The file size must not exceed 70MB.',
        ];
    }
}
