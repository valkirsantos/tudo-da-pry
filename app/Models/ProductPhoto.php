<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPhoto extends Model
{
    protected $fillable = [
        'product_id',
        'path_s3',
        'ordem',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
