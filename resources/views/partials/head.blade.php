<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<style>[x-cloak],[wire\:cloak]{display:none!important}</style>

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon-logo.png?v=1" type="image/png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
