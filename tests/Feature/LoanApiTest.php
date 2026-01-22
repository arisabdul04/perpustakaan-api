<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoanApiTest extends TestCase
{
    use RefreshDatabase;
    
    protected function authenticate()
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        return ['Authorization' => 'Bearer ' . $token];
    }

    /** @test */
    public function user_can_borrow_book()
    {
        $headers = $this->authenticate();

        $book = Book::factory()->create([
            'stock' => 3,
        ]);

        $response = $this->postJson('/api/borrow', [
            'book_id' => $book->id,
        ], $headers);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Book borrowed successfully',
            ]);

        $this->assertDatabaseHas('loans', [
            'book_id' => $book->id,
            'status'  => 'borrowed',
        ]);

        $this->assertDatabaseHas('books', [
            'id'    => $book->id,
            'stock' => 2,
        ]);
    }

    /** @test */
    public function cannot_borrow_book_if_out_of_stock()
    {
        $headers = $this->authenticate();

        $book = Book::factory()->create([
            'stock' => 0,
        ]);

        $response = $this->postJson('/api/borrow', [
            'book_id' => $book->id,
        ], $headers);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'message' => 'Out of stock',
            ]);
    }

    /** @test */
    public function user_can_return_book()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $book = Book::factory()->create([
            'stock' => 1,
        ]);

        $loan = Loan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status'  => 'borrowed',
        ]);

        $response = $this->putJson("/api/return/{$loan->id}", [], $this->authenticate());

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Book returned',
            ]);

        $this->assertDatabaseHas('loans', [
            'id'     => $loan->id,
            'status' => 'returned',
        ]);

        $this->assertDatabaseHas('books', [
            'id'    => $book->id,
            'stock' => 2,
        ]);
    }

    /** @test */
    public function user_can_view_their_loans()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Loan::factory()->count(2)->create([
            'user_id' => $user->id,
            'status'  => 'borrowed',
        ]);

        $response = $this->getJson('/api/my-loans', $this->authenticate());

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    /** @test */
    public function user_can_filter_loans_by_status()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Loan::factory()->create([
            'user_id' => $user->id,
            'status'  => 'borrowed',
        ]);

        Loan::factory()->create([
            'user_id' => $user->id,
            'status'  => 'returned',
        ]);

        $response = $this->getJson('/api/my-loans?status=returned', $this->authenticate());

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }
}
