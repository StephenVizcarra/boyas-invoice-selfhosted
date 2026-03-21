<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sender;
use App\Models\InvoiceCounter;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'recipient'                  => 'required|array',
            'recipient.name'             => 'required|string|max:255',
            'recipient.company'          => 'nullable|string|max:255',
            'recipient.address'          => 'nullable|string|max:255',
            'recipient.city_state_zip'   => 'nullable|string|max:255',
            'recipient.email'            => 'nullable|email|max:255',
            'line_items'                 => 'required|array|min:1',
            'line_items.*.description'   => 'required|string|max:500',
            'line_items.*.amount'        => 'required|numeric|min:0|max:9999999',
            'line_items.*.qty'           => 'nullable|numeric|min:0|max:9999',
            'line_items.*.rate'          => 'nullable|numeric|min:0|max:9999999',
            'notes'                      => 'nullable|string|max:1000',
        ]);

        $sender = Sender::find(1);

        // Increment first, then read — produces 1 on first call (matching previous behavior)
        $counter = InvoiceCounter::firstOrCreate(['id' => 1], ['counter' => 0]);
        $counter->increment('counter');
        $number = $counter->counter;

        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
        $total    = array_sum(array_column($request->line_items, 'amount'));
        $showQty  = collect($request->line_items)->contains(fn($item) => isset($item['qty']));

        // Embed logo as base64 if available
        $logoData = null;
        $logoMime = null;
        if (!empty($sender?->logo_path) && Storage::disk('local')->exists($sender->logo_path)) {
            $logoData = base64_encode(Storage::disk('local')->get($sender->logo_path));
            $ext      = pathinfo($sender->logo_path, PATHINFO_EXTENSION);
            $logoMime = $ext === 'jpg' ? 'image/jpeg' : 'image/' . $ext;
        }

        $pdf = Pdf::loadView('pdf.invoice', [
            'sender'        => $sender ? $sender->toArray() : [],
            'recipient'     => $request->recipient,
            'lineItems'     => $request->line_items,
            'invoiceNumber' => $invoiceNumber,
            'date'          => date('F j, Y'),
            'total'         => $total,
            'notes'         => $request->notes,
            'showQty'       => $showQty,
            'logoData'      => $logoData,
            'logoMime'      => $logoMime,
        ]);

        $filename = $invoiceNumber . '.pdf';
        return $pdf->download($filename)->header('X-Invoice-Number', $invoiceNumber);
    }
}
