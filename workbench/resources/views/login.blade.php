<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · Config SSO demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-full items-center justify-center bg-zinc-100 p-6 dark:bg-zinc-900">
    <div class="w-full max-w-sm space-y-6 rounded-2xl bg-white p-8 shadow dark:bg-zinc-800">
        <div class="text-center">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Config SSO demo</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Sign in with a configured provider.</p>
        </div>

        @if (session('status'))
            <div class="rounded-lg bg-amber-50 px-4 py-2 text-sm text-amber-800 dark:bg-amber-900/30 dark:text-amber-200">
                {{ session('status') }}
            </div>
        @endif

        {{-- The package component — this is the whole integration on the login page. --}}
        <x-config-sso::buttons />

        <p class="text-center text-xs text-zinc-400">
            <a href="{{ route('dev.login') }}" class="underline">Skip to admin UI (dev)</a>
        </p>
    </div>
</body>
</html>
