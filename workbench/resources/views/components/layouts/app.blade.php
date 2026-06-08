<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Config SSO demo' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
    @fluxAppearance
</head>
<body class="min-h-full bg-zinc-100 text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100">
    <div class="mx-auto max-w-5xl p-6 sm:p-10">
        <nav class="mb-8 flex items-center justify-between text-sm">
            <a href="{{ route('dashboard') }}" class="font-medium">← Dashboard</a>
            <span class="text-zinc-500 dark:text-zinc-400">{{ auth()->user()?->email }}</span>
        </nav>

        {{ $slot }}
    </div>

    @livewireScripts
    @fluxScripts
</body>
</html>
