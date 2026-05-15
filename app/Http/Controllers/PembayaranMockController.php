<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PriceList;
use Illuminate\Http\Request;


class PembayaranMockController extends Controller
{
    public function showDataPartner(Request $request, string $partnerId)
    {
        $partner = \App\Models\Partner::query()->findOrFail($partnerId);

        // Section 3: pricelist hanya untuk psikolog (counselor) dan pengacara (legal)
        $priceLists = collect();
        if (in_array($partner->partner_type, ['legal', 'counselor'], true)) {
            $priceLists = PriceList::query()
                ->where('partner_id', $partnerId)
                ->orderBy('price')
                ->get();
        }

        return view('pages.user.data_partner', [
            'partner' => $partner,
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
