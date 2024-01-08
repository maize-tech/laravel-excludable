<?php

namespace Maize\Excludable\Tests;

use Illuminate\Support\Facades\Event;
use Maize\Excludable\Models\Exclusion;
use Maize\Excludable\Tests\Events\ArticleExcludedEvent;
use Maize\Excludable\Tests\Models\Article;

class ExclusionTest extends TestCase
{
    /** @test */
    public function it_can_add_model_to_exclusions()
    {
        $articles = Article::factory(5)->create();

        $this->assertModelsCount(exclusions: 0, articles: 5);

        $articles[0]->addToExclusion();

        $this
            ->assertModelsCount(exclusions: 1, articles: 5)
            ->assertExcludableHas(model: $articles[0]);

        $articles[1]->addToExclusion();

        $this
            ->assertModelsCount(exclusions: 2, articles: 5)
            ->assertExcludableHas(model: $articles[1]);
    }

    /** @test */
    public function it_can_remove_model_from_exclusions()
    {
        $articles = Article::factory(5)->create();

        $this->assertModelsCount(exclusions: 0, articles: 5);

        $articles[0]->addToExclusion();
        $articles[1]->addToExclusion();

        $this->assertModelsCount(exclusions: 2, articles: 5);

        $articles[1]->removeFromExclusion();

        $this
            ->assertModelsCount(exclusions: 1, articles: 5)
            ->assertExcludableMissing(model: $articles[1]);

        $articles[0]->removeFromExclusion();

        $this
            ->assertModelsCount(exclusions: 0, articles: 5)
            ->assertExcludableMissing(model: $articles[0]);
    }

    /** @test */
    public function it_can_include_all_models()
    {
        $articles = Article::factory(5)->create();

        $articles[0]->addToExclusion();
        $articles[1]->addToExclusion();

        $this
            ->assertModelsCount(exclusions: 2, articles: 5)
            ->assertCount(3, Article::all());

        Article::includeAllModels();

        $this
            ->assertModelsCount(exclusions: 0, articles: 5)
            ->assertQueryCount(5, Article::query());
    }

    /** @test */
    public function it_can_exclude_all_models()
    {
        $articles = Article::factory(5)->create();

        $articles[0]->addToExclusion();

        $this
            ->assertModelsCount(exclusions: 1, articles: 5)
            ->assertQueryCount(4, Article::query());

        Article::excludeAllModels();

        $articles->first()->addToExclusion();

        $this
            ->assertModelsCount(exclusions: 1, articles: 5)
            ->assertExcludableHasWildcard(model: Article::class)
            ->assertQueryCount(0, Article::query());
    }

    /** @test */
    public function it_can_exclude_all_models_with_exception()
    {
        $articles = Article::factory(5)->create();

        Article::excludeAllModels([
            $articles[0],
            $articles[1],
        ]);

        $this
            ->assertExcludableHasWildcard(model: Article::class, data: ['type' => Exclusion::TYPE_EXCLUDE])
            ->assertExcludableHas(model: $articles[0], data: ['type' => Exclusion::TYPE_INCLUDE])
            ->assertExcludableHas(model: $articles[1], data: ['type' => Exclusion::TYPE_INCLUDE])
            ->assertQueryCount(2, Article::query())
            ->assertQueryCount(5, Article::withExcluded())
            ->assertQueryCount(2, Article::withoutExcluded())
            ->assertQueryCount(3, Article::onlyExcluded());
    }

    /** @test */
    public function it_can_list_all_models_with_exclusions()
    {
        $articles = Article::factory(5)->create();

        $articles->first()->addToExclusion();

        $this->assertQueryCount(5, Article::withExcluded());
    }

    /** @test */
    public function it_can_list_all_models_without_exclusions()
    {
        $articles = Article::factory(5)->create();

        $articles->first()->addToExclusion();

        $this->assertQueryCount(4, Article::withoutExcluded());

        $this->assertQueryCount(4, Article::withExcluded(false));
    }

    /** @test */
    public function it_can_list_only_excluded_models()
    {
        $articles = Article::factory(5)->create();

        $articles->first()->addToExclusion();

        $this->assertQueryCount(1, Article::onlyExcluded());
    }

    /** @test */
    public function it_should_return_true_if_model_is_excluded()
    {
        $articles = Article::factory(5)->create();

        $articles->first()->addToExclusion();

        $this->assertTrue($articles->first()->excluded());
    }

    /** @test */
    public function it_should_fire_excluded_event()
    {
        Event::fake();

        $article = Article::factory()->create();

        $article->addToExclusion();

        Event::assertDispatched(ArticleExcludedEvent::class);
    }

    /** @test */
    public function it_can_retrieve_exclusion_relation()
    {
        $article = Article::factory()->create();

        $article->addToExclusion();

        $this->assertCount(1, $article->exclusion()->get());

        Article::excludeAllModels();

        $this->assertCount(1, $article->exclusion()->get());
    }

    /** @test */
    public function it_can_remove_model_with_exclusions()
    {
        $articles = Article::factory(5)->create();

        // 0 exclude
        $this->assertModelsCount(exclusions: 0, articles: 5);

        $articles[0]->addToExclusion();
        $articles[1]->addToExclusion();

        // 2 exclude
        $this->assertModelsCount(exclusions: 2, articles: 5);

        $articles[1]->delete();

        // 1 exclude
        $this->assertModelsCount(exclusions: 1, articles: 4);

        $articles[0]->delete();

        // 0 exclude
        $this->assertModelsCount(exclusions: 0, articles: 3);

        Article::excludeAllModels();

        // 1 exclude wildcard
        $this->assertModelsCount(exclusions: 1, articles: 3);

        $articles[2]->delete();

        // 1 exclude wildcard
        $this->assertModelsCount(exclusions: 1, articles: 2);

        $articles[3]->removeFromExclusion();

        // 1 exclude wildcard + 1 include
        $this->assertModelsCount(exclusions: 2, articles: 2);

        $articles[3]->delete();

        // 1 exclude wildcard
        $this->assertModelsCount(exclusions: 1, articles: 1);

        $articles[4]->delete();

        // 1 exclude wildcard
        $this->assertModelsCount(exclusions: 1, articles: 0);
    }
}
