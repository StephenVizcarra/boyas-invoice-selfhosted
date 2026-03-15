<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JsonStorage;
use Illuminate\Support\Facades\Storage;

class SenderController extends Controller
{
    public function show()
    {
        $sender = JsonStorage::get('sender.json', [
            'name' => '', 'company' => '', 'address' => '',
            'city_state_zip' => '', 'email' => '', 'phone' => '',
            'logo_path' => null,
        ]);
        return response()->json($sender);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'company'       => 'nullable|string|max:255',
            'address'       => 'nullable|string|max:255',
            'city_state_zip'=> 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
            'phone'         => 'nullable|string|max:50',
        ]);

        $existing = JsonStorage::get('sender.json', []);
        $data['logo_path'] = $existing['logo_path'] ?? null;

        JsonStorage::put('sender.json', $data);
        return response()->json($data);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate(['logo' => 'required|image|max:2048']);

        $file = $request->file('logo');
        $ext  = $file->getClientOriginalExtension();
        $path = 'sender_logo.' . $ext;

        Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));

        $sender = JsonStorage::get('sender.json', []);
        $sender['logo_path'] = $path;
        JsonStorage::put('sender.json', $sender);

        return response()->json(['logo_path' => $path]);
    }
}
