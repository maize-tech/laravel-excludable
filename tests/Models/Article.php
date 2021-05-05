<?php

namespace HFarm\Excludable\Tests\Models;

use HFarm\Excludable\Excludable;
use HFarm\Excludable\Tests\Events\ArticleExcludedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    use Excludable;

    protected $dispatchesEvents = [
        'excluded' => ArticleExcludedEvent::class,
    ];
}
