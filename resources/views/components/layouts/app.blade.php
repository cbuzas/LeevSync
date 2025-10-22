<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Leev Sync</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @fluxAppearance
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">

<flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r rtl:border-r-0 rtl:border-l border-zinc-200 dark:border-zinc-700">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    <flux:brand href="#" logo="/assets/logo-light.svg" name="Leev Sync." class="px-2  dark:hidden" />
    <flux:brand href="#"  logo="/assets/logo.svg" name="Leev Sync." class="px-2 hidden dark:flex" />

    @livewire('profile-selector')


    <flux:navlist variant="outline">

        @foreach($mainMenu as $item)
            <flux:navlist.item :href="route($item['route'])" :icon="$item['icon']"  :current="request()->routeIs($item['route'].'*')" wire:navigate>
                {{ __($item['title']) }}
            </flux:navlist.item>
        @endforeach


    </flux:navlist>

    <flux:spacer />


    <flux:navlist variant="outline">
        @foreach($secondMenu as $item)
            @if($item['title'] === 'separator')
                <flux:separator variant="subtle"  />
                @continue
            @endif
            <flux:navlist.item variant="outline"  :href="route($item['route'])"  :current="request()->routeIs($item['route'].'*')" wire:navigate>
                {{ __($item['title']) }}
            </flux:navlist.item>
        @endforeach

    </flux:navlist>


</flux:sidebar>

<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

    <flux:spacer />

    <flux:dropdown position="top" alignt="start">
        <flux:profile avatar="https://fluxui.dev/img/demo/user.png" />

        <flux:menu>
            <flux:menu.radio.group>
                <flux:menu.radio checked>Olivia Martin</flux:menu.radio>
                <flux:menu.radio>Truly Delta</flux:menu.radio>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <flux:menu.item icon="arrow-right-start-on-rectangle">Logout</flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</flux:header>

<flux:main>

    {{ $main ?? $slot }}

</flux:main>

<x-notifications />

@fluxScripts

</body>
</html>
