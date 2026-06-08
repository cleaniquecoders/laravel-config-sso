@props([
    'providers' => null,
])

@php
    // Resolve active providers lazily so this component has zero required props.
    $items = $providers ?? \CleaniqueCoders\ConfigSso\Facades\ConfigSso::providers();
@endphp

@if ($items->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'flex flex-col gap-3']) }}>
        @foreach ($items as $provider)
            @php
                $label = $provider->button_label ?: 'Continue with '.$provider->name;
                $logo = $provider->show_logo ? $provider->icon : null;
                $isImage = $logo && (\Illuminate\Support\Str::startsWith($logo, ['http://', 'https://', '/']));
            @endphp

            <a
                href="{{ route('sso.redirect', $provider->driver) }}"
                class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700"
            >
                @if ($isImage)
                    <img src="{{ $logo }}" alt="" class="h-5 w-5 object-contain" />
                @endif
                <span>{{ $label }}</span>
            </a>
        @endforeach
    </div>
@endif
