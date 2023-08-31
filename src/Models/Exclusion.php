<?php

namespace Maize\Excludable\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exclusion extends Model
{
    use HasFactory;

    const TYPE_EXCLUDE = 'exclude';

    const TYPE_INCLUDE = 'include';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'type',
        'excludable_type',
        'excludable_id',
    ];
}
