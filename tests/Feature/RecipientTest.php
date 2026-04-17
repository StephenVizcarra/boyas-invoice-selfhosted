<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipientTest extends TestCase
{
    use RefreshDatabase;

    // --- GET /api/recipients ---

    public function test_get_recipients_returns_empty_array_on_fresh_database(): void
    {
        $this->getJson('/api/recipients')
            ->assertStatus(200)
            ->assertExactJson([]);
    }

    public function test_get_recipients_returns_saved_recipients(): void
    {
        $this->postJson('/api/recipients', ['name' => 'Alice', 'company' => 'Wonderland Inc']);
        $this->postJson('/api/recipients', ['name' => 'Bob']);

        $response = $this->getJson('/api/recipients');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['name' => 'Alice'])
            ->assertJsonFragment(['name' => 'Bob']);
    }

    public function test_get_recipients_does_not_expose_timestamps(): void
    {
        $this->postJson('/api/recipients', ['name' => 'Alice']);

        $recipients = $this->getJson('/api/recipients')->json();

        $this->assertArrayNotHasKey('created_at', $recipients[0]);
        $this->assertArrayNotHasKey('updated_at', $recipients[0]);
    }

    // --- POST /api/recipients (create) ---

    public function test_post_recipients_creates_new_recipient_with_r_prefixed_id(): void
    {
        $response = $this->postJson('/api/recipients', [
            'name' => 'Alice',
            'company' => 'Wonderland Inc',
            'email' => 'alice@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('name', 'Alice')
            ->assertJsonPath('company', 'Wonderland Inc')
            ->assertJsonPath('email', 'alice@example.com');

        $this->assertStringStartsWith('r_', $response->json('id'));
    }

    public function test_post_recipients_create_response_does_not_expose_timestamps(): void
    {
        $response = $this->postJson('/api/recipients', ['name' => 'Alice']);

        $response->assertStatus(200);
        $this->assertArrayNotHasKey('created_at', $response->json());
        $this->assertArrayNotHasKey('updated_at', $response->json());
    }

    public function test_post_recipients_requires_name(): void
    {
        $this->postJson('/api/recipients', ['company' => 'ACME'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    // --- POST /api/recipients (update) ---

    public function test_post_recipients_updates_existing_recipient(): void
    {
        $created = $this->postJson('/api/recipients', ['name' => 'Alice'])->json();
        $id = $created['id'];

        $this->postJson('/api/recipients', ['id' => $id, 'name' => 'Alice Updated']);

        $recipients = $this->getJson('/api/recipients')->json();
        $this->assertCount(1, $recipients);
        $this->assertEquals('Alice Updated', $recipients[0]['name']);
    }

    public function test_post_recipients_with_unknown_id_creates_new_row(): void
    {
        $this->postJson('/api/recipients', ['id' => 'r_nonexistent', 'name' => 'Ghost']);

        $this->getJson('/api/recipients')
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Ghost']);
    }

    // --- DELETE /api/recipients/{id} ---

    public function test_delete_recipient_returns_ok(): void
    {
        $id = $this->postJson('/api/recipients', ['name' => 'Alice'])->json('id');

        $this->deleteJson("/api/recipients/{$id}")
            ->assertStatus(200)
            ->assertExactJson(['ok' => true]);

        $this->getJson('/api/recipients')->assertExactJson([]);
    }

    public function test_delete_recipient_returns_404_for_unknown_id(): void
    {
        $this->deleteJson('/api/recipients/r_doesnotexist')
            ->assertStatus(404);
    }
}
