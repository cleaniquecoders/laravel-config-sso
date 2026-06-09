<?php

// Guards every `icon="…"` referenced in the package's Flux admin view against the
// set of icons Flux actually ships. Flux resolves icons via <flux:delegate-component>
// and THROWS "Flux component [icon.x] does not exist" at runtime for an unknown name
// (e.g. Lucide's `ellipsis`/`trash-2`/`eye-off` instead of Heroicons'
// `ellipsis-horizontal`/`trash`/`eye-slash`). That crash only surfaces under a full
// boot (`testbench serve`) — in the headless test harness Flux renders icons as no-ops,
// so an HTTP feature test can't tell a valid icon from a broken one. This static check
// can, so the admin UI never ships an icon name Flux can't render.

$fluxIconStubs = dirname(__DIR__, 2).'/vendor/livewire/flux/stubs/resources/views/flux/icon';

it('only references Flux icons that exist', function () use ($fluxIconStubs) {
    expect(is_dir($fluxIconStubs))->toBeTrue("Flux icon stubs not found at {$fluxIconStubs}");

    $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/livewire/sso-providers.blade.php');

    // Static `icon="name"` plus the quoted tokens inside dynamic
    // `icon="{{ $cond ? 'eye-slash' : 'eye' }}"` expressions.
    preg_match_all('/icon="([a-z][a-z0-9-]*)"/', $view, $static);
    preg_match_all('/icon="\{\{(.+?)\}\}"/', $view, $dynamic);

    $names = $static[1];
    foreach ($dynamic[1] as $expression) {
        preg_match_all("/'([a-z][a-z0-9-]*)'/", $expression, $tokens);
        $names = array_merge($names, $tokens[1]);
    }

    $names = array_values(array_unique($names));
    expect($names)->not->toBeEmpty();

    foreach ($names as $name) {
        expect(is_file("{$fluxIconStubs}/{$name}.blade.php"))
            ->toBeTrue("Flux has no icon [{$name}] — use a valid Heroicon name (Flux ships Heroicons, not Lucide).");
    }
});
