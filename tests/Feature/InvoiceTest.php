<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private array $validPayload = [
        'recipient' => [
            'name'           => 'Test Client',
            'company'        => 'Test Co',
            'address'        => '456 Client Ave',
            'city_state_zip' => 'Testville, TX 75001',
            'email'          => 'client@example.com',
        ],
        'line_items' => [
            ['description' => 'Consulting services', 'amount' => 500.00],
        ],
        'notes' => 'Thank you for your business.',
    ];

    // --- POST /api/invoice/generate ---

    public function test_generate_returns_pdf_content_type(): void
    {
        $response = $this->post('/api/invoice/generate', $this->validPayload);

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_generate_sets_invoice_number_header(): void
    {
        $response = $this->post('/api/invoice/generate', $this->validPayload);

        $response->assertStatus(200);
        $invoiceNumber = $response->headers->get('X-Invoice-Number');

        $this->assertNotNull($invoiceNumber);
        $this->assertMatchesRegularExpression(
            '/^INV-\d{4}-\d{4}$/',
            $invoiceNumber,
            "Invoice number '{$invoiceNumber}' does not match INV-YYYY-NNNN format"
        );
    }

    public function test_generate_first_invoice_is_number_0001(): void
    {
        $response = $this->post('/api/invoice/generate', $this->validPayload);

        $year = date('Y');
        $response->assertHeader('X-Invoice-Number', "INV-{$year}-0001");
    }

    public function test_generate_increments_invoice_counter_on_each_call(): void
    {
        $year = date('Y');

        $first = $this->post('/api/invoice/generate', $this->validPayload);
        $first->assertHeader('X-Invoice-Number', "INV-{$year}-0001");

        $second = $this->post('/api/invoice/generate', $this->validPayload);
        $second->assertHeader('X-Invoice-Number', "INV-{$year}-0002");
    }

    public function test_generate_requires_recipient_name(): void
    {
        $payload = $this->validPayload;
        $payload['recipient']['name'] = '';

        $this->postJson('/api/invoice/generate', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['recipient.name']);
    }

    public function test_generate_requires_at_least_one_line_item(): void
    {
        $payload = $this->validPayload;
        $payload['line_items'] = [];

        $this->postJson('/api/invoice/generate', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['line_items']);
    }
}
