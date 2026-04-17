<?php

namespace App\Http\Controllers;

use App\Models\Sender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SenderController extends Controller
{
    public function show()
    {
        return response()->json($this->senderResponse(Sender::first()));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city_state_zip' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        // updateOrCreate only updates the columns in $validated — logo_path is untouched
        $sender = Sender::updateOrCreate(['id' => 1], $validated);

        return response()->json($this->senderResponse($sender));
    }

    public function uploadLogo(Request $request)
    {
        $request->validate(['logo' => 'required|image|max:2048']);

        $file = $request->file('logo');
        $ext = $file->extension();
        $path = 'sender_logo.'.$ext;

        Storage::disk('local')->putFileAs('', $file, $path);

        Sender::updateOrCreate(['id' => 1], ['logo_path' => $path]);

        return response()->json(['logo_path' => $path]);
    }

    public function getLogo()
    {
        $sender = Sender::first();
        if (! $sender?->logo_path || ! Storage::disk('local')->exists($sender->logo_path)) {
            abort(404);
        }

        $ext = strtolower(pathinfo($sender->logo_path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        return response(Storage::disk('local')->get($sender->logo_path), 200)
            ->header('Content-Type', $mime)
            ->header('Cache-Control', 'no-store');
    }

    public function deleteLogo()
    {
        $sender = Sender::first();
        if ($sender) {
            if ($sender->logo_path && Storage::disk('local')->exists($sender->logo_path)) {
                Storage::disk('local')->delete($sender->logo_path);
            }
            $sender->update(['logo_path' => null]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Build the consistent sender response shape.
     * Returns empty strings (not null) for text fields — matches frontend expectations.
     * Never exposes DB internals (id, created_at, updated_at).
     */
    private function senderResponse(?Sender $sender): array
    {
        return [
            'name' => $sender?->name ?? '',
            'company' => $sender?->company ?? '',
            'address' => $sender?->address ?? '',
            'city_state_zip' => $sender?->city_state_zip ?? '',
            'email' => $sender?->email ?? '',
            'phone' => $sender?->phone ?? '',
            'logo_path' => $sender?->logo_path ?? null,
        ];
    }
}
