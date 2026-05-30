@php
    // show back only when we're inside dashboard flow (user/admin/partner)
    // and not on the initial dashboard page (/dashboard).
    $isDashboardInitial = in_array(request()->route()?->getName(), ['dashboard', 'admin.index', 'partner.index']);

    // Default fallback to dashboard if opened directly (no history)
    $fallbackUrl = auth()->check() ? route('dashboard') : url('/');

    // If backUrl is provided explicitly (e.g., from controller/view), we use it as fallback.
    // In many pages, $backUrl is assigned request()->headers->get('referer').
    $resolvedFallbackUrl = (isset($backUrl) && $backUrl) ? $backUrl : $fallbackUrl;

    // Prevent redirecting to the same page if referer was current url
    if (trim($resolvedFallbackUrl, '/') === trim(url()->current(), '/')) {
        $resolvedFallbackUrl = $fallbackUrl;
    }

    $resolvedBackLabel = $backLabel ?? 'Kembali';
    $hideBrand = !$isDashboardInitial;
    
    // Only show back button if not on initial dashboard
    $showBackButton = !$isDashboardInitial;

    $authName = auth()->check() ? auth()->user()->name : '';
    $mobileAuthName = mb_strlen($authName) > 14 ? mb_substr($authName, 0, 14) : $authName;
@endphp

<nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
        <div class="flex items-center gap-3">
            @if($showBackButton)
                <a href="{{ $resolvedFallbackUrl }}" 
                   class="text-gray-400 hover:text-gray-700 text-sm transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    {{ $resolvedBackLabel }}
                </a>
                <div class="w-px h-4 bg-gray-200"></div>
            @endif

            @if(!$hideBrand)
                <a href="{{ auth()->check() ? route('dashboard') : '/' }}" class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-red-700 rounded-lg flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944z" clip-rule="evenodd"/></svg>
                    </div>
                    <span class="font-bold text-gray-900 text-sm">Safora</span>
                </a>
            @endif
        </div>
        <div class="flex items-center gap-4">
            @auth
                <div class="flex items-center">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center text-gray-900 text-sm font-medium hover:text-red-700 transition gap-1 focus:outline-none">
                                <span class="sm:hidden">{{ $mobileAuthName }}</span>
                                <span class="hidden sm:inline">{{ $authName }}</span>
                                <svg class="fill-current h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link href="/settings">
                                Pengaturan
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    Keluar
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            @else
                <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 text-sm">Masuk</a>
                <a href="{{ route('register') }}" class="bg-gray-900 hover:bg-gray-700 text-white text-sm px-4 py-2 rounded-lg font-semibold transition">Daftar</a>
            @endauth
        </div>
    </div>
</nav>

@include('partials.toasts')

@if(session('system_error'))
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            showToast('{{ addslashes(session('system_error')) }}', 'error');
        });
    </script>
@endif
