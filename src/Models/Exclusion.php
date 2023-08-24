<?php

namespace Maize\Excludable\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exclusion extends Model
{
    use HasFactory;

    protected $fillable = [
        'excludable_type',
        'excludable_id',
    ];
}
