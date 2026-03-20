<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipient;

class RecipientController extends Controller
{
    public function index()
    {
        return response()->json(
            Recipient::all()->map(fn($r) => $this->recipientResponse($r))
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'company'        => 'nullable|string|max:255',
            'address'        => 'nullable|string|max:255',
            'city_state_zip' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
        ]);

        $id = $request->input('id') ?? uniqid('r_', true);

        $recipient = Recipient::updateOrCreate(['id' => $id], $validated);

        return response()->json($this->recipientResponse($recipient));
    }

    public function destroy(string $id)
    {
        Recipient::findOrFail($id)->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Build the consistent recipient response shape.
     * Never exposes DB internals (created_at, updated_at).
     */
    private function recipientResponse(Recipient $r): array
    {
        return [
            'id'             => $r->id,
            'name'           => $r->name,
            'company'        => $r->company ?? '',
            'address'        => $r->address ?? '',
            'city_state_zip' => $r->city_state_zip ?? '',
            'email'          => $r->email ?? '',
        ];
    }
}
