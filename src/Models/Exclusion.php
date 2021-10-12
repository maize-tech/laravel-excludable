<?php

namespace Maize\Excludable\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Exclusion extends Model
{
    use HasFactory;

    protected $fillable = [
        'excludable_type',
        'excludable_id',
    ];

    public function excludable(): MorphTo
    {
        return $this->morphTo();
    }
}
