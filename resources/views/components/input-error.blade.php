@props(['messages'])
@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-red-500 text-xs space-y-0.5 mt-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
