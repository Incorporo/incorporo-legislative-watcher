<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'document_type',
        'title',
        'description',
        'url',
        'local_path',
        'file_hash',
        'file_size',
        'mime_type',
        'downloaded',
        'downloaded_at',
        'download_attempts',
        'download_error',
        'text_extracted',
        'extracted_text',
        'extracted_at',
        'version',
        'document_date',
        'metadata',
    ];

    protected $casts = [
        'downloaded' => 'boolean',
        'downloaded_at' => 'datetime',
        'text_extracted' => 'boolean',
        'extracted_at' => 'datetime',
        'document_date' => 'date',
        'metadata' => 'array',
    ];

    public function bill()
    {
        return $this->belongsTo(LegislativeBill::class, 'bill_id');
    }

    public function scopeDownloaded($query)
    {
        return $query->where('downloaded', true);
    }

    public function scopeTextExtracted($query)
    {
        return $query->where('text_extracted', true);
    }
}
