<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/search');
        $response->assertStatus(200);
    }

    public function test_search_returns_results(): void
    {
        $user = User::factory()->create();
        Project::factory()->create(['owner_id' => $user->id, 'name' => 'Alpha Project']);

        $response = $this->actingAs($user)->get('/search?q=Alpha');
        $response->assertStatus(200);
    }

    public function test_reports_page_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/reports');
        $response->assertStatus(200);
    }
}
