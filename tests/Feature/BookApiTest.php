<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate()
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        return ['Authorization' => 'Bearer ' . $token];
    }

    /** @test */
    public function can_get_list_of_books()
    {
        $headers = $this->authenticate();

        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/books', $headers);

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    /** @test */
    public function can_filter_books_by_category()
    {
        $headers = $this->authenticate();

        $category = Category::factory()->create();
        Book::factory()->create(['category_id' => $category->id]);
        Book::factory()->create();

        $response = $this->getJson("/api/books?category_id={$category->id}", $headers);

        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }

    /** @test */
    public function can_create_book()
    {
        $headers = $this->authenticate();
        $category = Category::factory()->create();

        $payload = [
            'category_id' => $category->id,
            'title' => 'Test Book',
            'author' => 'John Doe',
            'stock' => 10,
            'published_at' => now()->toDateString(),
        ];

        $response = $this->postJson('/api/books', $payload, $headers);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Test Book']);

        $this->assertDatabaseHas('books', ['title' => 'Test Book']);
    }

    /** @test */
    public function validation_error_when_creating_book()
    {
        $headers = $this->authenticate();

        $response = $this->postJson('/api/books', [], $headers);

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors']);
    }

    /** @test */
    public function can_show_book_detail()
    {
        $headers = $this->authenticate();
        $book = Book::factory()->create();

        $response = $this->getJson("/api/books/{$book->id}", $headers);

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $book->id]);
    }

    /** @test */
    public function can_update_book()
    {
        $headers = $this->authenticate();
        $book = Book::factory()->create();

        $response = $this->putJson("/api/books/{$book->id}", [
            'title' => 'Updated Title'
        ], $headers);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Updated Title']);

        $this->assertDatabaseHas('books', ['title' => 'Updated Title']);
    }

    /** @test */
    public function can_delete_book_without_active_loan()
    {
        $headers = $this->authenticate();
        $book = Book::factory()->create();

        $response = $this->deleteJson("/api/books/{$book->id}", [], $headers);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}
