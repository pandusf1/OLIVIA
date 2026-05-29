@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-green-600']) }} x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition.duration.500ms>
        {{ $status }}
    </div>
@endif
