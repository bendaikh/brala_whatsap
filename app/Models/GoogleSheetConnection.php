<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleSheetConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'name',
        'spreadsheet_id',
        'spreadsheet_url',
        'sheet_tab',
        'webhook_url',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function isConfigured(): bool
    {
        return $this->is_enabled && !empty($this->webhook_url);
    }

    public static function extractSpreadsheetId(?string $urlOrId): ?string
    {
        if (empty($urlOrId)) {
            return null;
        }

        $urlOrId = trim($urlOrId);

        if (preg_match('#/spreadsheets/d/([a-zA-Z0-9-_]+)#', $urlOrId, $matches)) {
            return $matches[1];
        }

        if (preg_match('#^[a-zA-Z0-9-_]{20,}$#', $urlOrId)) {
            return $urlOrId;
        }

        return null;
    }
}
