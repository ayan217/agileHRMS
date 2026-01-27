<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('assets/favicons/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('assets/favicons/favicon-192x192.png') }}">

    <!-- Apple Touch Icon (iOS Home Screen) -->
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('assets/favicons/apple-touch-icon.png') }}">

    <!-- Android / Chrome -->
    <link rel="manifest" href="{{ asset('assets/favicons/site.webmanifest') }}">

    <!-- Fallback -->
    <link rel="shortcut icon" href="{{ asset('assets/favicons/favicon.ico') }}">


    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />


    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/css/work-timer.css', 'resources/css/holiday-calendar.css', 'resources/css/admin-attendance.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <livewire:layout.navigation />

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('alert', (data) => {
                showAlert(data.message, data.type ?? 'success');
            });
        });
    </script>

</body>

</html>
