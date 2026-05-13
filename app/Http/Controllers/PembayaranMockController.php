<?php

namespace App\Http\Controllers;

use App\Models\PriceList;
use Illuminate\Http\Request;

class PembayaranMockController extends Controller
{
    public function show(Request $request, string $partnerId)
    {
        $priceLists = PriceList::query()
            ->where('partner_id', $partnerId)
            ->orderBy('price')
            ->get();

        return view('pages.user.pembayaran_mock', [
            'partnerId' => $partnerId,
            'priceLists' => $priceLists,
        ]);
    }

    public function pay(Request $request)
    {
        $request->validate([
            'price_list_id' => 'required|integer',
        ]);

        // simpan pilihan di session
        session([
            'selected_price_list_id' => $request->price_list_id,
        ]);

        $priceList = PriceList::findOrFail($request->price_list_id);
        return redirect()->route('chat.start', ['partnerId' => $priceList->partner_id]);
    }
}
