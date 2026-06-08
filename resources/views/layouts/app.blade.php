{{--
    Self-contained fallback layout for the full-page admin UI so the route works
    in ANY app out of the box. Hosts should point `config-sso.admin.layout` at
    their own layout (which already loads Tailwind + Livewire + Flux), or embed
    <livewire:config-sso.admin /> inside their own page instead.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SSO Providers' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
    @fluxAppearance
</head>
<body class="min-h-full bg-zinc-100 text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100">
    <main class="mx-auto max-w-5xl p-6 sm:p-10">
        {{ $slot }}
    </main>

    @livewireScripts
    @fluxScripts
</body>
</html>
