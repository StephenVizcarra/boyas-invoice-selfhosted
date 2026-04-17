<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceCounter;
use App\Models\Sender;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'recipient' => 'required|array',
            'recipient.name' => 'required|string|max:255',
            'recipient.company' => 'nullable|string|max:255',
            'recipient.address' => 'nullable|string|max:255',
            'recipient.city_state_zip' => 'nullable|string|max:255',
            'recipient.email' => 'nullable|email|max:255',
            'line_items' => 'required|array|min:1',
            'line_items.*.description' => 'required|string|max:500',
            'line_items.*.amount' => 'required|numeric|min:0|max:9999999',
            'line_items.*.qty' => 'nullable|numeric|min:0|max:9999',
            'line_items.*.rate' => 'nullable|numeric|min:0|max:9999999',
            'notes' => 'nullable|string|max:1000',
        ]);

        $sender = Sender::first();

        // Increment first, then read — produces 1 on first call (matching previous behavior)
        $counter = InvoiceCounter::firstOrCreate(['id' => 1], ['counter' => 0]);
        $counter->increment('counter');
        $number = $counter->counter;

        $invoiceNumber = 'INV-'.now()->format('Y').'-'.str_pad($number, 4, '0', STR_PAD_LEFT);
        $total = array_sum(array_column($request->line_items, 'amount'));
        $showQty = collect($request->line_items)->contains(fn ($item) => isset($item['qty']));

        // Embed logo as base64 if available
        $logoData = null;
        $logoMime = null;
        if (! empty($sender?->logo_path) && Storage::disk('local')->exists($sender->logo_path)) {
            $logoData = base64_encode(Storage::disk('local')->get($sender->logo_path));
            $ext = strtolower(pathinfo($sender->logo_path, PATHINFO_EXTENSION));
            $logoMime = match ($ext) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'webp' => 'image/webp',
                default => 'application/octet-stream',
            };
        }

        $pdf = Pdf::loadView('pdf.invoice', [
            'sender' => $sender ? $sender->toArray() : [],
            'recipient' => $request->recipient,
            'lineItems' => $request->line_items,
            'invoiceNumber' => $invoiceNumber,
            'date' => now()->format('F j, Y'),
            'total' => $total,
            'notes' => $request->notes,
            'showQty' => $showQty,
            'logoData' => $logoData,
            'logoMime' => $logoMime,
        ]);

        $filename = $invoiceNumber.'.pdf';
        $bytes = $pdf->output();

        // Persist history — non-fatal; the download must succeed even if this fails
        try {
            Storage::disk('local')->put("invoices/{$invoiceNumber}.pdf", $bytes);
            Invoice::create([
                'number' => $invoiceNumber,
                'recipient' => $request->recipient,
                'line_items' => $request->line_items,
                'total' => $total,
                'notes' => $request->notes,
                'sender_snapshot' => [
                    'name' => $sender?->name,
                    'company' => $sender?->company,
                    'address' => $sender?->address,
                    'city_state_zip' => $sender?->city_state_zip,
                    'email' => $sender?->email,
                    'phone' => $sender?->phone,
                    'logo_data' => $logoData,
                    'logo_mime' => $logoMime,
                ],
                'generated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Invoice history persistence failed: '.$e->getMessage());
        }

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'X-Invoice-Number' => $invoiceNumber,
        ]);
    }
}
