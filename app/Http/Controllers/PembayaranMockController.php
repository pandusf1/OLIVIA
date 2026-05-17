<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PriceList;
use App\Models\ChatThread;
use App\Models\UserPartnerPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PembayaranMockController extends Controller
{
    public function showDataPartner(Request $request, string $partnerId)
    {
        $partner = Partner::findOrFail($partnerId);

        $priceLists = collect();
        if (in_array($partner->partner_type, ['legal', 'counselor'], true)) {
            $priceLists = PriceList::query()
                ->where('partner_id', $partnerId)
                ->orderBy('price')
                ->get();
        }

        return view('pages.user.data_partner', [
            'partner'    => $partner,
            'priceLists' => $priceLists,
        ]);
    }

    /**
     * Halaman konfirmasi & pembayaran untuk satu price list
     */
    public function showPembayaran(string $priceListId)
    {
        $priceList = PriceList::with('partner')->findOrFail($priceListId);

        return view('pages.user.pembayaran_mock', [
            'priceList' => $priceList,
        ]);
    }

    /**
     * Proses pembayaran (demo) → simpan di session → redirect ke chat
     */
    public function pay(Request $request)
    {
        $request->validate([
            'price_list_id' => 'required',
        ]);

        $priceList = PriceList::findOrFail($request->price_list_id);

        UserPartnerPayment::create([
            'user_id' => auth()->id(),
            'partner_id' => $priceList->partner_id,
            'price_list_id' => $priceList->id,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        ChatThread::firstOrCreate(
            ['user_id' => auth()->id(), 'partner_id' => $priceList->partner_id],
            ['id' => (string) Str::uuid(), 'last_message_at' => now()]
        );

        return response()->json(['ok' => true, 'partner_id' => $priceList->partner_id]);
    }
}
