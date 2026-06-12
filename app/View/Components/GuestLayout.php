<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    /**
     * Dapatkan view / konten yang merepresentasikan komponen.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
