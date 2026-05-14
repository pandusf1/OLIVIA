<?php

namespace App\Http\Controllers;

use App\Models\UserLocation;
use Illuminate\Http\Request;

class UserLocationController extends Controller
{
    public function reload(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $lat = (float) $request->input('latitude');
        $lng = (float) $request->input('longitude');

        $userId = auth()->id();

        // Lebih aman: firstOrNew + save (updateOrCreate sebelumnya tidak terbaca saat dicek via relasi).
        $loc = UserLocation::where('user_id', $userId)->first();
        if (!$loc) {
            $loc = new UserLocation();
            $loc->user_id = $userId;
        }

        $loc->latitude = $lat;
        $loc->longitude = $lng;
        $loc->save();

        return response()->json([
            'ok' => true,
            'latitude' => $lat,
            'longitude' => $lng,
            'saved_location_id' => $loc->id ?? null,
        ]);
    }
}

