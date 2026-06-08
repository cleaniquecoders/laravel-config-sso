<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard · Config SSO demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-full bg-zinc-100 p-6 dark:bg-zinc-900">
    <div class="mx-auto max-w-xl space-y-4 rounded-2xl bg-white p-8 shadow dark:bg-zinc-800">
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Signed in 🎉</h1>
        <p class="text-sm text-zinc-600 dark:text-zinc-300">
            You are logged in as <strong>{{ auth()->user()?->email }}</strong>.
        </p>
        <div class="flex gap-3 pt-2">
            <a href="{{ route('config-sso.admin') }}"
               class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-900">
                Manage SSO providers
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600 dark:text-zinc-100">
                    Log out
                </button>
            </form>
        </div>
    </div>
</body>
</html>
