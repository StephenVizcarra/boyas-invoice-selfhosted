<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Support\Facades\Storage;

class InvoiceHistoryController extends Controller
{
    public function index()
    {
        $invoices = Invoice::orderByDesc('generated_at')->orderByDesc('id')->get();

        return response()->json($invoices->map(fn ($inv) => $this->invoiceResponse($inv)));
    }

    public function show(string $number)
    {
        $invoice = Invoice::where('number', $number)->firstOrFail();
        $path = "invoices/{$number}.pdf";

        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return response(Storage::disk('local')->get($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$number.'.pdf"',
        ]);
    }

    public function destroy(string $number)
    {
        $invoice = Invoice::where('number', $number)->firstOrFail();

        $path = "invoices/{$number}.pdf";
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }

        $invoice->delete();

        return response()->noContent();
    }

    private function invoiceResponse(Invoice $invoice): array
    {
        return [
            'number' => $invoice->number,
            'generated_at' => $invoice->generated_at->toISOString(),
            'total' => $invoice->total,
            'notes' => $invoice->notes,
            'recipient' => $invoice->recipient,
            'line_items' => $invoice->line_items,
            'sender' => [
                'name' => $invoice->sender_snapshot['name'] ?? '',
                'company' => $invoice->sender_snapshot['company'] ?? '',
            ],
        ];
    }
}
