<?php

namespace Maize\Excludable\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Maize\Excludable\Excludable;
use Maize\Excludable\Tests\Events\ArticleExcludedEvent;
use Maize\Excludable\Tests\Events\ArticleExcludingEvent;

class Article extends Model
{
    use Excludable;
    use HasFactory;

    protected $dispatchesEvents = [
        'excluding' => ArticleExcludingEvent::class,
        'excluded' => ArticleExcludedEvent::class,
    ];
}
