<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JsonStorage;

class RecipientController extends Controller
{
    public function index()
    {
        return response()->json(JsonStorage::get('recipients.json', []));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'company'       => 'nullable|string|max:255',
            'address'       => 'nullable|string|max:255',
            'city_state_zip'=> 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
        ]);

        $recipients = JsonStorage::get('recipients.json', []);

        // If an id is provided, update the existing recipient
        if ($request->has('id')) {
            $recipients = array_map(function ($r) use ($request, $data) {
                return $r['id'] === $request->id ? array_merge($r, $data) : $r;
            }, $recipients);
        } else {
            $data['id'] = uniqid('r_', true);
            $recipients[] = $data;
        }

        JsonStorage::put('recipients.json', array_values($recipients));
        return response()->json($data);
    }

    public function destroy(string $id)
    {
        $recipients = JsonStorage::get('recipients.json', []);
        $recipients = array_filter($recipients, fn($r) => $r['id'] !== $id);
        JsonStorage::put('recipients.json', array_values($recipients));
        return response()->json(['ok' => true]);
    }
}
