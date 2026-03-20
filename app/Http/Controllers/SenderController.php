<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sender;
use Illuminate\Support\Facades\Storage;

class SenderController extends Controller
{
    public function show()
    {
        return response()->json($this->senderResponse(Sender::find(1)));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'company'        => 'nullable|string|max:255',
            'address'        => 'nullable|string|max:255',
            'city_state_zip' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:50',
        ]);

        // updateOrCreate only updates the columns in $validated — logo_path is untouched
        $sender = Sender::updateOrCreate(['id' => 1], $validated);

        return response()->json($this->senderResponse($sender));
    }

    public function uploadLogo(Request $request)
    {
        $request->validate(['logo' => 'required|image|max:2048']);

        $file = $request->file('logo');
        $ext  = $file->extension();
        $path = 'sender_logo.' . $ext;

        Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));

        Sender::updateOrCreate(['id' => 1], ['logo_path' => $path]);

        return response()->json(['logo_path' => $path]);
    }

    /**
     * Build the consistent sender response shape.
     * Returns empty strings (not null) for text fields — matches frontend expectations.
     * Never exposes DB internals (id, created_at, updated_at).
     */
    private function senderResponse(?Sender $sender): array
    {
        return [
            'name'           => $sender?->name ?? '',
            'company'        => $sender?->company ?? '',
            'address'        => $sender?->address ?? '',
            'city_state_zip' => $sender?->city_state_zip ?? '',
            'email'          => $sender?->email ?? '',
            'phone'          => $sender?->phone ?? '',
            'logo_path'      => $sender?->logo_path ?? null,
        ];
    }
}
