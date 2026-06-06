<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use App\Models\PriceList;
use App\Models\ChatThread;
use App\Models\UserMitraPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PembayaranMockController extends Controller
{
    public function showDataMitra(Request $request, string $mitraId)
    {
        $mitra = Mitra::findOrFail($mitraId);

        $priceLists = collect();
        if (in_array($mitra->mitra_type, ['legal', 'counselor'], true)) {
            $priceLists = PriceList::query()
                ->where('mitra_id', $mitraId)
                ->orderBy('price')
                ->get();
        }

        return view('pages.user.data_mitra', [
            'mitra'      => $mitra,
            'priceLists' => $priceLists,
        ]);
    }

    /**
     * Halaman konfirmasi & pembayaran untuk satu atau beberapa price list
     */
    public function showPembayaran(string $priceListIds)
    {
        $ids = explode(',', $priceListIds);
        $priceLists = PriceList::with('mitra')->whereIn('id', $ids)->get();

        if ($priceLists->isEmpty()) {
            abort(404, 'Layanan tidak ditemukan.');
        }

        $mitra = $priceLists->first()->mitra;
        $totalPrice = $priceLists->sum('price');

        return view('pages.user.pembayaran_mock', [
            'priceLists'   => $priceLists,
            'totalPrice'   => $totalPrice,
            'mitra'        => $mitra,
            'priceListIds' => $priceListIds,
        ]);
    }

    /**
     * Proses pembayaran (demo) → simpan di session → redirect ke chat
     */
    public function pay(Request $request)
    {
        $request->validate([
            'price_list_ids' => 'required',
            'payment_method' => 'nullable|string',
        ]);

        $ids = is_array($request->price_list_ids)
            ? $request->price_list_ids
            : explode(',', $request->price_list_ids);

        $priceLists = PriceList::whereIn('id', $ids)->get();

        if ($priceLists->isEmpty()) {
            return response()->json(['error' => 'Layanan tidak ditemukan.'], 404);
        }

        $mitraId = $priceLists->first()->mitra_id;
        $status = $request->payment_method === 'negotiation' ? 'negotiation' : 'paid';

        foreach ($priceLists as $pl) {
            UserMitraPayment::create([
                'user_id' => auth()->id(),
                'mitra_id' => $mitraId,
                'price_list_id' => $pl->id,
                'status' => $status,
                'paid_at' => now(),
            ]);
        }

        ChatThread::firstOrCreate(
            ['user_id' => auth()->id(), 'mitra_id' => $mitraId],
            ['id' => (string) Str::uuid(), 'last_message_at' => now()]
        );

        return response()->json(['ok' => true, 'mitra_id' => $mitraId]);
    }
}
