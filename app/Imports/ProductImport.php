<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\FileUpload;
use App\Models\Product;

class ProductImport implements 
    ToModel, 
    WithHeadingRow, 
    WithBatchInserts, 
    WithChunkReading,
    SkipsOnError,
    WithEvents
{
    use SkipsErrors, RemembersRowNumber;

    protected $fileUpload;
    protected $processedRows = 0;
    protected $totalRows = 0;

    public function __construct(FileUpload $fileUpload)
    {
        $this->fileUpload = $fileUpload;
    }

    protected function cleanUtf8($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = (string) $value;
        
        // Remove BOM (Byte Order Mark) if present
        $value = preg_replace('/^\x{FEFF}/u', '', $value);
        
        // Remove non-UTF-8 characters and convert encoding
        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        
        // Remove invisible control characters
        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
        
        return trim($value);
    }

    public function model(array $row)
    {
        $row = array_map(function($value) {
            return $this->cleanUtf8($value);
        }, $row);

        if (empty($row['unique_key'])) {
            return null;
        }

        $this->processedRows++;

        if ($this->processedRows % 10 === 0) {
            $this->fileUpload->update([
                'processed_rows' => $this->processedRows
            ]);
        }

        $productData = [
            'unique_key'                        => $row['unique_key'] ?? null,
            'product_title'                     => $row['product_title'] ?? null,
            'product_description'               => $row['product_description'] ?? null,
            'style'                             => $row['style'] ?? null,
            'available_sizes'                   => $row['available_sizes'] ?? null,
            'brand_logo_image'                  => $row['brand_logo_image'] ?? null,
            'thumbnail_image'                   => $row['thumbnail_image'] ?? null,
            'color_swatch_image'                => $row['color_swatch_image'] ?? null,
            'product_image'                     => $row['product_image'] ?? null,
            'spec_sheet'                        => $row['spec_sheet'] ?? null,
            'price_text'                        => $row['price_text'] ?? null,
            'suggested_price'                   => !empty($row['suggested_price']) ? (float) $row['suggested_price'] : null,
            'category_name'                     => $row['category_name'] ?? null,
            'subcategory_name'                  => $row['subcategory_name'] ?? null,
            'color_name'                        => $row['color_name'] ?? null,
            'color_square_image'                => $row['color_square_image'] ?? null,
            'color_product_image'               => $row['color_product_image'] ?? null,
            'color_product_image_thumbnail'     => $row['color_product_image_thumbnail'] ?? null,
            'size'                              => $row['size'] ?? null,
            'qty'                               => !empty($row['qty']) ? (int) $row['qty'] : null,
            'piece_weight'                      => !empty($row['piece_weight']) ? (float) $row['piece_weight'] : null,
            'piece_price'                       => !empty($row['piece_price']) ? (float) $row['piece_price'] : null,
            'dozens_price'                      => !empty($row['dozens_price']) ? (float) $row['dozens_price'] : null,
            'case_price'                        => !empty($row['case_price']) ? (float) $row['case_price'] : null,
            'price_group'                       => $row['price_group'] ?? null,
            'case_size'                         => !empty($row['case_size']) ? (int) $row['case_size'] : null,
            'inventory_key'                     => $row['inventory_key'] ?? null,
            'size_index'                        => !empty($row['size_index']) ? (int) $row['size_index'] : null,
            'sanmar_mainframe_color'            => $row['sanmar_mainframe_color'] ?? null,
            'mill'                              => $row['mill'] ?? null,
            'product_status'                    => $row['product_status'] ?? null,
            'companion_styles'                  => $row['companion_styles'] ?? null,
            'msrp'                              => !empty($row['msrp']) ? (float) $row['msrp'] : null,
            'map_pricing'                       => !empty($row['map_pricing']) ? (float) $row['map_pricing'] : null,
            'front_model_image_url'             => $row['front_model_image_url'] ?? null,
            'back_model_image'                  => $row['back_model_image'] ?? null,
            'front_flat_image'                  => $row['front_flat_image'] ?? null,
            'back_flat_image'                   => $row['back_flat_image'] ?? null,
            'product_measurements'              => $row['product_measurements'] ?? null,
            'pms_color'                         => $row['pms_color'] ?? null,
            'gtin'                              => $row['gtin'] ?? null,
        ];

        return Product::updateOrCreate(
            ['unique_key' => $productData['unique_key']],
            $productData
        );
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function onError(\Throwable $e)
    {
        Log::error('Row import error: ' . $e->getMessage());
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function(BeforeImport $event) {
                
                $totalRows = $event->getReader()->getTotalRows();
                
                if (!empty($totalRows)) {
                    $this->totalRows = array_sum($totalRows) - 1; // Subtract header row
                    $this->fileUpload->update(['total_rows' => $this->totalRows]);
                }
            },
            AfterImport::class => function(AfterImport $event) {
                
                $this->fileUpload->update([
                    'processed_rows' => $this->processedRows
                ]);
            },
        ];
    }

    public function headingRow(): int
    {
        return 1;
    }
}
