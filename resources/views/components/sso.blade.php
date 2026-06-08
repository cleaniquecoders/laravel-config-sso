@if ($providers->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'flex flex-col gap-3']) }}>
        @foreach ($providers as $ssoProvider)
            @php
                $label = $ssoProvider->button_label ?: 'Continue with '.$ssoProvider->name;
                $logo = $ssoProvider->show_logo ? $ssoProvider->icon : null;
                $isImage = $logo && (\Illuminate\Support\Str::startsWith($logo, ['http://', 'https://', '/']));
            @endphp

            <a
                href="{{ route('sso.redirect', $ssoProvider->driver) }}"
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
