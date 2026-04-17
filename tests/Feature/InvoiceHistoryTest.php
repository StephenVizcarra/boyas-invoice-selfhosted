<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvoiceHistoryTest extends TestCase
{
    use RefreshDatabase;

    private array $generatePayload = [
        'recipient' => [
            'name' => 'Test Client',
            'company' => 'Test Co',
            'address' => '456 Client Ave',
            'city_state_zip' => 'Testville, TX 75001',
            'email' => 'client@example.com',
        ],
        'line_items' => [
            ['description' => 'Consulting services', 'amount' => 500.00],
        ],
        'notes' => 'Thank you for your business.',
    ];

    // --- GET /api/invoices ---

    public function test_get_invoices_returns_empty_array_on_fresh_database(): void
    {
        $this->getJson('/api/invoices')
            ->assertStatus(200)
            ->assertExactJson([]);
    }

    public function test_get_invoices_returns_tiles_after_generating(): void
    {
        Storage::fake('local');

        $this->postJson('/api/invoice/generate', $this->generatePayload)->assertStatus(200);

        $this->getJson('/api/invoices')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.recipient.name', 'Test Client')
            ->assertJsonPath('0.total', '500.00');
    }

    public function test_get_invoices_returns_tiles_in_reverse_chronological_order(): void
    {
        Storage::fake('local');

        $this->postJson('/api/invoice/generate', $this->generatePayload)->assertStatus(200);
        $this->postJson('/api/invoice/generate', $this->generatePayload)->assertStatus(200);

        $year = date('Y');

        $this->getJson('/api/invoices')
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonPath('0.number', "INV-{$year}-0002")
            ->assertJsonPath('1.number', "INV-{$year}-0001");
    }

    public function test_get_invoices_does_not_expose_id_timestamps_or_base64_logo(): void
    {
        Storage::fake('local');

        $this->postJson('/api/invoice/generate', $this->generatePayload)->assertStatus(200);

        $response = $this->getJson('/api/invoices')->assertStatus(200);

        $tile = $response->json('0');

        $this->assertArrayNotHasKey('id', $tile);
        $this->assertArrayNotHasKey('created_at', $tile);
        $this->assertArrayNotHasKey('updated_at', $tile);
        $this->assertArrayNotHasKey('sender_snapshot', $tile);
        $this->assertArrayHasKey('sender', $tile);
        $this->assertArrayNotHasKey('logo_data', $tile['sender']);
    }

    public function test_get_invoices_includes_expected_shape(): void
    {
        Storage::fake('local');

        $this->postJson('/api/invoice/generate', $this->generatePayload)->assertStatus(200);

        $this->getJson('/api/invoices')
            ->assertStatus(200)
            ->assertJsonStructure([[
                'number',
                'generated_at',
                'total',
                'notes',
                'recipient',
                'line_items',
                'sender' => ['name', 'company'],
            ]]);
    }

    // --- GET /api/invoices/{number} ---

    public function test_get_invoice_returns_pdf_by_number(): void
    {
        Storage::fake('local');

        $this->postJson('/api/invoice/generate', $this->generatePayload)->assertStatus(200);

        $year = date('Y');

        $response = $this->get("/api/invoices/INV-{$year}-0001");

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_get_invoice_returns_404_for_unknown_number(): void
    {
        $this->get('/api/invoices/INV-2026-9999')->assertStatus(404);
    }

    // --- DELETE /api/invoices/{number} ---

    public function test_delete_invoice_removes_row_and_file(): void
    {
        Storage::fake('local');

        $this->postJson('/api/invoice/generate', $this->generatePayload)->assertStatus(200);

        $year = date('Y');
        $number = "INV-{$year}-0001";

        Storage::disk('local')->assertExists("invoices/{$number}.pdf");

        $this->deleteJson("/api/invoices/{$number}")->assertStatus(204);

        $this->assertDatabaseMissing('invoices', ['number' => $number]);
        Storage::disk('local')->assertMissing("invoices/{$number}.pdf");
    }

    public function test_delete_invoice_returns_404_for_unknown_number(): void
    {
        $this->deleteJson('/api/invoices/INV-2026-9999')->assertStatus(404);
    }

    public function test_deleted_invoice_no_longer_appears_in_list(): void
    {
        Storage::fake('local');

        $this->postJson('/api/invoice/generate', $this->generatePayload)->assertStatus(200);

        $year = date('Y');

        $this->deleteJson("/api/invoices/INV-{$year}-0001")->assertStatus(204);

        $this->getJson('/api/invoices')
            ->assertStatus(200)
            ->assertExactJson([]);
    }

    // --- Integration: generate persists history ---

    public function test_generate_creates_invoice_history_row(): void
    {
        Storage::fake('local');

        $this->postJson('/api/invoice/generate', $this->generatePayload)->assertStatus(200);

        $year = date('Y');

        $this->assertDatabaseHas('invoices', [
            'number' => "INV-{$year}-0001",
        ]);
    }

    public function test_generate_writes_pdf_to_storage(): void
    {
        Storage::fake('local');

        $this->postJson('/api/invoice/generate', $this->generatePayload)->assertStatus(200);

        $year = date('Y');

        Storage::disk('local')->assertExists("invoices/INV-{$year}-0001.pdf");
    }

    public function test_generate_stores_correct_metadata(): void
    {
        Storage::fake('local');

        $this->postJson('/api/invoice/generate', $this->generatePayload)->assertStatus(200);

        $invoice = Invoice::first();

        $this->assertSame('Test Client', $invoice->recipient['name']);
        $this->assertSame('Test Co', $invoice->recipient['company']);
        $this->assertSame('Consulting services', $invoice->line_items[0]['description']);
        $this->assertEquals(500.00, (float) $invoice->total);
        $this->assertSame('Thank you for your business.', $invoice->notes);
    }
}
