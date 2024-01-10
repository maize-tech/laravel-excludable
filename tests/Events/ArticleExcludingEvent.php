<?php

namespace Maize\Excludable\Tests\Events;

use Maize\Excludable\Tests\Models\Article;

class ArticleExcludingEvent
{
    public function __construct(
        public Article $article
    ) {
    }
}
