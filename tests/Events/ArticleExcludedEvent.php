<?php

namespace Maize\Excludable\Tests\Events;

use Maize\Excludable\Tests\Models\Article;

class ArticleExcludedEvent
{
    public function __construct(
        public Article $article
    ) {
    }
}
