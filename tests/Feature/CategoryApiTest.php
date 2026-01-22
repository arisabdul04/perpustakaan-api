<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate()
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        return ['Authorization' => 'Bearer ' . $token];
    }


    /** @test */
    public function can_get_category_list()
    {
        $headers = $this->authenticate();

        Category::factory()->count(3)->create();

        $response = $this->withHeaders($headers)->getJson('/api/categories');

        $response
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function can_create_category()
    {
        $headers = $this->authenticate();

        $payload = Category::factory()->make()->toArray();

        $response = $this->withHeaders($headers)->postJson('/api/categories', $payload);

        $response
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => $payload['name'],
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => $payload['name'],
        ]);
    }

    /** @test */
    public function can_show_category_detail()
    {
        $headers = $this->authenticate();

        $category = Category::factory()->create();

        $response = $this->withHeaders($headers)->getJson("/api/categories/{$category->id}");

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => $category->name,
            ]);
    }

    /** @test */
    public function can_update_category()
    {
        $headers = $this->authenticate();

        $category = Category::factory()->create();

        $payload = [
            'name' => 'Updated Category',
        ];

        $response = $this->withHeaders($headers)->putJson("/api/categories/{$category->id}", $payload);

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Category',
            ]);

        $this->assertDatabaseHas('categories', [
            'id'   => $category->id,
            'name' => 'Updated Category',
        ]);
    }

    /** @test */
    public function can_delete_category()
    {
        $headers = $this->authenticate();

        $category = Category::factory()->create();

        $response = $this->withHeaders($headers)->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }
}
