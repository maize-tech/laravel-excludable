<?php

namespace Maize\Excludable\Tests\Events;

use Maize\Excludable\Tests\Models\Article;

class ArticleExcludedEvent
{
    public Article $article;

    public function __construct(Article $article)
    {
        $this->article = $article;
    }
}
