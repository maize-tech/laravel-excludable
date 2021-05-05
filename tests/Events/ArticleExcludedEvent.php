<?php

namespace HFarm\Excludable\Tests\Events;

use HFarm\Excludable\Tests\Models\Article;

class ArticleExcludedEvent
{
    public Article $article;

    public function __construct(Article $article)
    {
        $this->article = $article;
    }
}
