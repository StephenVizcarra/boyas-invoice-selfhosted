<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JsonStorage;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'recipient'              => 'required|array',
            'recipient.name'         => 'required|string',
            'line_items'               => 'required|array|min:1',
            'line_items.*.description' => 'required|string',
            'line_items.*.amount'      => 'required|numeric|min:0',
            'line_items.*.qty'         => 'nullable|numeric|min:0',
            'line_items.*.rate'        => 'nullable|numeric|min:0',
            'notes'                  => 'nullable|string|max:1000',
        ]);

        $sender     = JsonStorage::get('sender.json', []);
        $counter    = JsonStorage::get('invoice_counter.json', ['counter' => 1]);
        $number     = $counter['counter'];

        // Persist incremented counter
        JsonStorage::put('invoice_counter.json', ['counter' => $number + 1]);

        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
        $total    = array_sum(array_column($request->line_items, 'amount'));
        $showQty  = collect($request->line_items)->contains(fn($item) => isset($item['qty']));

        // Embed logo as base64 if available
        $logoData = null;
        $logoMime = null;
        if (!empty($sender['logo_path']) && Storage::disk('local')->exists($sender['logo_path'])) {
            $logoData = base64_encode(Storage::disk('local')->get($sender['logo_path']));
            $ext      = pathinfo($sender['logo_path'], PATHINFO_EXTENSION);
            $logoMime = $ext === 'jpg' ? 'image/jpeg' : 'image/' . $ext;
        }

        $pdf = Pdf::loadView('pdf.invoice', [
            'sender'        => $sender,
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
