<?php

namespace Maize\Excludable\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Maize\Excludable\Excludable;
use Maize\Excludable\Tests\Events\ArticleExcludedEvent;

class Article extends Model
{
    use Excludable;
    use HasFactory;

    protected $dispatchesEvents = [
        'excluded' => ArticleExcludedEvent::class,
    ];
}
