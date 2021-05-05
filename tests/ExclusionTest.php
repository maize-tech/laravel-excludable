<?php

namespace HFarm\Excludable\Tests;

use HFarm\Excludable\Models\Exclusion;
use HFarm\Excludable\Tests\Events\ArticleExcludedEvent;
use HFarm\Excludable\Tests\Models\Article;
use Illuminate\Support\Facades\Event;

class ExclusionTest extends TestCase
{
    private string $exclusionsTable;

    public function setUp(): void
    {
        parent::setUp();

        $this->exclusionsTable = (new Exclusion)->getTable();
    }

    /** @test */
    public function it_can_add_model_to_exclusions()
    {
        $articles = Article::factory(5)->create();

        $this->assertDatabaseCount($this->exclusionsTable, 0);

        $articles[0]->addToExclusion();

        $this->assertDatabaseCount($this->exclusionsTable, 1);

        $this->assertDatabaseHas($this->exclusionsTable, [
            'excludable_type' => Article::class,
            'excludable_id' => $articles[0]->id,
        ]);

        $articles[1]->addToExclusion();

        $this->assertDatabaseCount($this->exclusionsTable, 2);

        $this->assertDatabaseHas($this->exclusionsTable, [
            'excludable_type' => Article::class,
            'excludable_id' => $articles[1]->id,
        ]);
    }

    /** @test */
    public function it_can_remove_model_form_exclusions()
    {
        $articles = Article::factory(5)->create();

        $this->assertDatabaseCount($this->exclusionsTable, 0);

        $articles[0]->addToExclusion();
        $articles[1]->addToExclusion();

        $this->assertDatabaseCount($this->exclusionsTable, 2);

        $articles[1]->removeFromExclusion();

        $this->assertDatabaseCount($this->exclusionsTable, 1);

        $this->assertDatabaseMissing($this->exclusionsTable, [
            'excludable_type' => Article::class,
            'excludable_id' => $articles[1]->id,
        ]);

        $articles[0]->removeFromExclusion();

        $this->assertDatabaseCount($this->exclusionsTable, 0);

        $this->assertDatabaseMissing($this->exclusionsTable, [
            'excludable_type' => Article::class,
            'excludable_id' => $articles[0]->id,
        ]);
    }

    /** @test */
    public function it_can_list_all_models_with_exclusions()
    {
        $articles = Article::factory(5)->create();

        $articles->first()->addToExclusion();

        $this->assertCount(5, Article::withExcluded()->get());
    }

    /** @test */
    public function it_can_list_all_models_without_exclusions()
    {
        $articles = Article::factory(5)->create();

        $articles->first()->addToExclusion();

        $this->assertCount(4, Article::withoutExcluded()->get());

        $this->assertCount(4, Article::withExcluded(false)->get());
    }

    /** @test */
    public function it_can_list_only_excluded_models()
    {
        $articles = Article::factory(5)->create();

        $articles->first()->addToExclusion();

        $this->assertCount(1, Article::onlyExcluded()->get());
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
}
