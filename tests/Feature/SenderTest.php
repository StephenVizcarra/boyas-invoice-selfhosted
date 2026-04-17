<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SenderTest extends TestCase
{
    use RefreshDatabase;

    // --- GET /api/sender ---

    public function test_get_sender_returns_empty_string_defaults_when_no_profile_exists(): void
    {
        $response = $this->getJson('/api/sender');

        $response->assertStatus(200)
            ->assertExactJson([
                'name' => '',
                'company' => '',
                'address' => '',
                'city_state_zip' => '',
                'email' => '',
                'phone' => '',
                'logo_path' => null,
            ]);
    }

    public function test_get_sender_returns_saved_profile(): void
    {
        $this->postJson('/api/sender', ['name' => 'Jane Doe', 'company' => 'ACME']);

        $response = $this->getJson('/api/sender');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Jane Doe', 'company' => 'ACME']);
    }

    // --- POST /api/sender ---

    public function test_post_sender_saves_and_returns_profile(): void
    {
        $payload = [
            'name' => 'Jane Doe',
            'company' => 'ACME Corp',
            'address' => '123 Main St',
            'city_state_zip' => 'Springfield, IL 62701',
            'email' => 'jane@example.com',
            'phone' => '555-1234',
        ];

        $response = $this->postJson('/api/sender', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Jane Doe',
                'company' => 'ACME Corp',
                'address' => '123 Main St',
                'city_state_zip' => 'Springfield, IL 62701',
                'email' => 'jane@example.com',
                'phone' => '555-1234',
                'logo_path' => null,
            ]);

        // Response must not expose DB internals
        $response->assertJsonMissingPath('id');
        $response->assertJsonMissingPath('created_at');
        $response->assertJsonMissingPath('updated_at');
    }

    public function test_post_sender_requires_name(): void
    {
        $this->postJson('/api/sender', ['company' => 'ACME'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_post_sender_preserves_logo_path_when_updating_profile(): void
    {
        // Simulate a logo already saved (use create() — GD not required)
        Storage::fake('local');
        $file = UploadedFile::fake()->create('logo.png', 100, 'image/png');
        $this->post('/api/sender/logo', ['logo' => $file]);

        // Now update the profile text fields
        $this->postJson('/api/sender', ['name' => 'Updated Name']);

        // Logo path must survive the profile update
        $this->getJson('/api/sender')
            ->assertStatus(200)
            ->assertJsonPath('logo_path', 'sender_logo.png');
    }

    // --- POST /api/sender/logo ---

    public function test_upload_logo_stores_file_and_returns_logo_path(): void
    {
        Storage::fake('local');

        // Use create() with image MIME type — does not require the GD extension
        $file = UploadedFile::fake()->create('logo.png', 100, 'image/png');

        $response = $this->post('/api/sender/logo', ['logo' => $file]);

        $response->assertStatus(200)
            ->assertJsonPath('logo_path', 'sender_logo.png');

        Storage::disk('local')->assertExists('sender_logo.png');
    }

    public function test_upload_logo_requires_an_image(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        // withHeaders ensures validation failure returns JSON 422, not a redirect
        $this->withHeaders(['Accept' => 'application/json'])
            ->post('/api/sender/logo', ['logo' => $file])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['logo']);
    }

    // --- GET /api/sender/logo ---

    public function test_get_logo_returns_404_when_no_logo_exists(): void
    {
        $this->get('/api/sender/logo')->assertStatus(404);
    }

    public function test_get_logo_returns_image_after_upload(): void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('logo.png', 100, 'image/png');
        $this->post('/api/sender/logo', ['logo' => $file]);

        $response = $this->get('/api/sender/logo');

        $response->assertStatus(200);
        $this->assertStringContainsString('image/png', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    // --- DELETE /api/sender/logo ---

    public function test_delete_logo_removes_file_and_clears_path(): void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('logo.png', 100, 'image/png');
        $this->post('/api/sender/logo', ['logo' => $file]);

        $this->getJson('/api/sender')->assertJsonPath('logo_path', 'sender_logo.png');

        $this->delete('/api/sender/logo')
            ->assertStatus(200)
            ->assertExactJson(['success' => true]);

        Storage::disk('local')->assertMissing('sender_logo.png');
        $this->getJson('/api/sender')->assertJsonPath('logo_path', null);
    }

    public function test_delete_logo_is_idempotent_when_no_logo_exists(): void
    {
        $this->delete('/api/sender/logo')
            ->assertStatus(200)
            ->assertExactJson(['success' => true]);
    }
}
