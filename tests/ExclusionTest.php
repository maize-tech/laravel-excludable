<?php

namespace Maize\Excludable\Tests;

use Illuminate\Support\Facades\Event;
use Maize\Excludable\Models\Exclusion;
use Maize\Excludable\Tests\Events\ArticleExcludedEvent;
use Maize\Excludable\Tests\Events\ArticleExcludingEvent;
use Maize\Excludable\Tests\Models\Article;

class ExclusionTest extends TestCase
{
    /** @test */
    public function it_can_add_model_to_exclusions()
    {
        $articles = Article::factory(5)->create();

        $this
            ->assertModelsCount(exclusions: 0, articles: 5)
            ->assertExcludableMissing(model: $articles)
            ->assertQueryCount(count: 5, query: Article::query())
            ->assertQueryCount(5, Article::withExcluded())
            ->assertQueryCount(5, Article::withoutExcluded())
            ->assertQueryCount(0, Article::onlyExcluded());

        $articles[0]->addToExclusion();

        $this
            ->assertModelsCount(exclusions: 1, articles: 5)
            ->assertExcludableHas(model: $articles[0])
            ->assertExcludableMissing(model: $articles, shift: 1)
            ->assertQueryCount(count: 4, query: Article::query())
            ->assertQueryCount(5, Article::withExcluded())
            ->assertQueryCount(4, Article::withoutExcluded())
            ->assertQueryCount(1, Article::onlyExcluded());

        $articles[1]->addToExclusion();

        $this
            ->assertModelsCount(exclusions: 2, articles: 5)
            ->assertExcludableHas(model: $articles[1])
            ->assertExcludableMissing(model: $articles, shift: 2)
            ->assertQueryCount(count: 3, query: Article::query())
            ->assertQueryCount(5, Article::withExcluded())
            ->assertQueryCount(3, Article::withoutExcluded())
            ->assertQueryCount(2, Article::onlyExcluded());
    }

    /** @test */
    public function it_can_add_model_to_exclusions_without_duplicates()
    {
        $articles = Article::factory(5)->create();

        $this
            ->assertModelsCount(exclusions: 0, articles: 5)
            ->assertExcludableMissing(model: $articles)
            ->assertQueryCount(count: 5, query: Article::query());

        $articles[0]->addToExclusion();

        $this
            ->assertModelsCount(exclusions: 1, articles: 5)
            ->assertExcludableHas(model: $articles[0])
            ->assertExcludableMissing(model: $articles, shift: 1)
            ->assertQueryCount(count: 4, query: Article::query());

        $articles[0]->addToExclusion();

        $this
            ->assertModelsCount(exclusions: 1, articles: 5)
            ->assertExcludableHas(model: $articles[0])
            ->assertExcludableMissing(model: $articles, shift: 1)
            ->assertQueryCount(count: 4, query: Article::query());
    }

    /** @test */
    public function it_can_add_wildcard_exclusion()
    {
        $articles = Article::factory(5)->create();

        $this
            ->assertModelsCount(exclusions: 0, articles: 5)
            ->assertExcludableMissing(model: $articles)
            ->assertQueryCount(count: 5, query: Article::query())
            ->assertQueryCount(5, Article::withExcluded())
            ->assertQueryCount(5, Article::withoutExcluded())
            ->assertQueryCount(0, Article::onlyExcluded());

        Article::excludeAllModels();

        $this
            ->assertModelsCount(exclusions: 1, articles: 5)
            ->assertExcludableHasWildcard(model: Article::class)
            ->assertExcludableMissing(model: $articles)
            ->assertQueryCount(count: 0, query: Article::query())
            ->assertQueryCount(5, Article::withExcluded())
            ->assertQueryCount(0, Article::withoutExcluded())
            ->assertQueryCount(5, Article::onlyExcluded());
    }

    /** @test */
    public function it_can_add_wildcard_exclusion_without_duplicates()
    {
        $articles = Article::factory(5)->create();

        $this
            ->assertModelsCount(exclusions: 0, articles: 5)
            ->assertExcludableMissing(model: $articles)
            ->assertQueryCount(count: 5, query: Article::query());

        Article::excludeAllModels();

        $this
            ->assertModelsCount(exclusions: 1, articles: 5)
            ->assertExcludableHasWildcard(model: Article::class)
            ->assertExcludableMissing(model: $articles)
            ->assertQueryCount(count: 0, query: Article::query());

        $articles[0]->addToExclusion();

        $this
            ->assertModelsCount(exclusions: 1, articles: 5)
            ->assertExcludableHasWildcard(model: Article::class)
            ->assertExcludableMissing(model: $articles)
            ->assertQueryCount(count: 0, query: Article::query());
    }

    /** @test */
    public function it_can_add_wildcard_exclusion_with_exceptions()
    {
        $articles = Article::factory(5)->create();

        $this
            ->assertModelsCount(exclusions: 0, articles: 5)
            ->assertExcludableMissing(model: $articles)
            ->assertQueryCount(count: 5, query: Article::query())
            ->assertQueryCount(5, Article::withExcluded())
            ->assertQueryCount(5, Article::withoutExcluded())
            ->assertQueryCount(0, Article::onlyExcluded());

        Article::excludeAllModels([
            $articles[0],
        ]);

        $this
            ->assertModelsCount(exclusions: 2, articles: 5)
            ->assertExcludableHasWildcard(model: Article::class)
            ->assertExcludableHas(model: $articles[0], data: ['type' => Exclusion::TYPE_INCLUDE])
            ->assertExcludableMissing(model: $articles, shift: 1)
            ->assertQueryCount(count: 1, query: Article::query())
            ->assertQueryCount(5, Article::withExcluded())
            ->assertQueryCount(1, Article::withoutExcluded())
            ->assertQueryCount(4, Article::onlyExcluded());

        Article::excludeAllModels([
            $articles[1],
            $articles[2],
        ]);

        $this
            ->assertModelsCount(exclusions: 3, articles: 5)
            ->assertExcludableHasWildcard(model: Article::class)
            ->assertExcludableHas(model: [$articles[1], $articles[2]], data: ['type' => Exclusion::TYPE_INCLUDE])
            ->assertExcludableMissing(model: [$articles[0], $articles[3], $articles[4]])
            ->assertQueryCount(count: 2, query: Article::query())
            ->assertQueryCount(5, Article::withExcluded())
            ->assertQueryCount(2, Article::withoutExcluded())
            ->assertQueryCount(3, Article::onlyExcluded());

        Article::excludeAllModels();

        $this
            ->assertModelsCount(exclusions: 1, articles: 5)
            ->assertExcludableHasWildcard(model: Article::class)
            ->assertExcludableMissing(model: $articles[0])
            ->assertQueryCount(count: 0, query: Article::query())
            ->assertQueryCount(5, Article::withExcluded())
            ->assertQueryCount(0, Article::withoutExcluded())
            ->assertQueryCount(5, Article::onlyExcluded());
    }

    /** @test */
    public function it_can_remove_model_from_exclusions()
    {
        $articles = Article::factory(5)->create();

        $this
            ->assertModelsCount(exclusions: 0, articles: 5)
            ->assertExcludableMissing(model: $articles)
            ->assertQueryCount(count: 5, query: Article::query());

        $articles[0]->addToExclusion();
        $articles[1]->addToExclusion();
        $articles[2]->addToExclusion();

        $this
            ->assertModelsCount(exclusions: 3, articles: 5)
            ->assertExcludableHas(model: $articles, take: 3)
            ->assertExcludableMissing(model: $articles, shift: 3)
            ->assertQueryCount(count: 2, query: Article::query());

        $articles[1]->removeFromExclusion();

        $this
            ->assertModelsCount(exclusions: 2, articles: 5)
            ->assertExcludableHas(model: [$articles[0], $articles[2]])
            ->assertExcludableMissing(model: [$articles[1], $articles[3], $articles[4]])
            ->assertQueryCount(count: 3, query: Article::query());

        $articles[0]->removeFromExclusion();

        $this
            ->assertModelsCount(exclusions: 1, articles: 5)
            ->assertExcludableHas(model: $articles[2])
            ->assertExcludableMissing(model: [$articles[0], $articles[1], $articles[3], $articles[4]])
            ->assertQueryCount(count: 4, query: Article::query());

        $articles[2]->removeFromExclusion();

        $this
            ->assertModelsCount(exclusions: 0, articles: 5)
            ->assertExcludableMissing(model: $articles)
            ->assertQueryCount(count: 5, query: Article::query());

        Article::excludeAllModels();

        $this
            ->assertModelsCount(exclusions: 1, articles: 5)
            ->assertExcludableHasWildcard(model: Article::class)
            ->assertQueryCount(count: 0, query: Article::query());

        $articles[0]->removeFromExclusion();

        $this
            ->assertModelsCount(exclusions: 2, articles: 5)
            ->assertExcludableHasWildcard(model: Article::class)
            ->assertExcludableHas(model: $articles[0], data: ['type' => Exclusion::TYPE_INCLUDE])
            ->assertExcludableMissing(model: $articles, shift: 1)
            ->assertQueryCount(count: 1, query: Article::query());

        $articles[1]->removeFromExclusion();

        $this
            ->assertModelsCount(exclusions: 3, articles: 5)
            ->assertExcludableHasWildcard(model: Article::class)
            ->assertExcludableHas(model: $articles, data: ['type' => Exclusion::TYPE_INCLUDE], take: 2)
            ->assertExcludableMissing(model: $articles, shift: 2)
            ->assertQueryCount(count: 2, query: Article::query());
    }

    /** @test */
    public function it_can_include_all_models()
    {
        $articles = Article::factory(5)->create();

        $this
            ->assertModelsCount(exclusions: 0, articles: 5)
            ->assertExcludableMissing(model: $articles)
            ->assertQueryCount(count: 5, query: Article::query());

        $articles[0]->addToExclusion();
        $articles[1]->addToExclusion();
        $articles[2]->addToExclusion();

        $this
            ->assertModelsCount(exclusions: 3, articles: 5)
            ->assertExcludableHas(model: $articles, take: 3)
            ->assertExcludableMissing(model: $articles, shift: 3)
            ->assertQueryCount(count: 2, query: Article::query());

        Article::includeAllModels();

        $this
            ->assertModelsCount(exclusions: 0, articles: 5)
            ->assertExcludableMissing(model: $articles)
            ->assertQueryCount(count: 5, query: Article::query());

        Article::excludeAllModels([
            $articles[1],
            $articles[2],
        ]);

        $this
            ->assertModelsCount(exclusions: 3, articles: 5)
            ->assertExcludableHasWildcard(model: Article::class)
            ->assertExcludableHas(model: [$articles[1], $articles[2]], data: ['type' => Exclusion::TYPE_INCLUDE])
            ->assertExcludableMissing(model: [$articles[0], $articles[3], $articles[4]])
            ->assertQueryCount(count: 2, query: Article::query());

        Article::includeAllModels();

        $this
            ->assertModelsCount(exclusions: 0, articles: 5)
            ->assertExcludableMissing(model: $articles)
            ->assertQueryCount(count: 5, query: Article::query());
    }

    /** @test */
    public function it_can_check_exclusions()
    {
        $articles = Article::factory(5)->create();

        $this->assertFalse(Article::hasExclusionWildcard());
        $articles->each(fn ($article) => $this->assertFalse($article->excluded()));

        $articles[0]->addToExclusion();

        $this->assertFalse(Article::hasExclusionWildcard());
        $this->assertTrue($articles[0]->excluded());
        $this->assertTrue($articles[0]->excluded());
        $this->assertFalse($articles[1]->excluded());
        $this->assertFalse($articles[2]->excluded());
        $this->assertFalse($articles[3]->excluded());
        $this->assertFalse($articles[4]->excluded());

        $articles[1]->addToExclusion();

        $this->assertFalse(Article::hasExclusionWildcard());
        $this->assertTrue($articles[0]->excluded());
        $this->assertTrue($articles[1]->excluded());
        $this->assertFalse($articles[2]->excluded());
        $this->assertFalse($articles[3]->excluded());
        $this->assertFalse($articles[4]->excluded());

        Article::excludeAllModels();

        $this->assertTrue(Article::hasExclusionWildcard());
        $articles->each(fn ($article) => $this->assertTrue($article->excluded()));

        Article::excludeAllModels([
            $articles[0],
            $articles[1],
        ]);

        $this->assertTrue(Article::hasExclusionWildcard());
        $this->assertFalse($articles[0]->excluded());
        $this->assertFalse($articles[1]->excluded());
        $this->assertTrue($articles[2]->excluded());
        $this->assertTrue($articles[3]->excluded());
        $this->assertTrue($articles[4]->excluded());
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

    /** @test */
    public function it_should_fire_exclusion_event()
    {
        Event::fake();

        $articles = Article::factory(5)->create();

        $articles[0]->addToExclusion();

        Event::assertDispatched(ArticleExcludingEvent::class);
        Event::assertDispatched(ArticleExcludedEvent::class);
    }

    /** @test */
    public function it_should_fire_exclusion_event_with_wildcard()
    {
        Event::fake();

        $articles = Article::factory(5)->create();

        Article::excludeAllModels([
            $articles[0],
        ]);

        $articles[0]->addToExclusion();

        Event::assertDispatched(ArticleExcludingEvent::class);
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
    public function it_can_list_all_models_with_exclusions()
    {
        $articles = Article::factory(5)->create();

        $articles[0]->addToExclusion();

        $this->assertQueryCount(5, Article::withExcluded());

        Article::excludeAllModels();

        $this->assertQueryCount(5, Article::withExcluded());

        Article::excludeAllModels([
            $articles[0],
            $articles[1],
        ]);

        $this->assertQueryCount(5, Article::withExcluded());
    }

    /** @test */
    public function it_can_list_all_models_without_exclusions()
    {
        $articles = Article::factory(5)->create();

        $articles[0]->addToExclusion();

        $this->assertQueryCount(4, Article::withoutExcluded());

        $this->assertQueryCount(4, Article::withExcluded(false));

        Article::excludeAllModels();

        $this->assertQueryCount(0, Article::withoutExcluded());

        $this->assertQueryCount(0, Article::withExcluded(false));

        Article::excludeAllModels([
            $articles[0],
            $articles[1],
        ]);

        $this->assertQueryCount(2, Article::withoutExcluded());

        $this->assertQueryCount(2, Article::withExcluded(false));
    }

    /** @test */
    public function it_can_list_only_excluded_models()
    {
        $articles = Article::factory(5)->create();

        $articles[0]->addToExclusion();

        $this->assertQueryCount(1, Article::onlyExcluded());

        Article::excludeAllModels();

        $this->assertQueryCount(5, Article::onlyExcluded());

        Article::excludeAllModels([
            $articles[0],
            $articles[1],
        ]);

        $this->assertQueryCount(3, Article::onlyExcluded());
    }
}
