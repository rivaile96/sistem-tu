<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistem TU') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body { 
                font-family: 'Inter', sans-serif;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">
        <div class="min-h-screen flex flex-col sm:justify-center items-center p-4 sm:p-6">
            
            <!-- Logo -->
            <div class="mb-4 sm:mb-6">
                <a href="/" class="flex flex-col items-center gap-2 sm:gap-3">
                   <div class="bg-[#0ea5e9] p-3 sm:p-4 rounded-xl sm:rounded-2xl shadow-lg shadow-sky-200">
                        <svg class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                   </div>
                   <span class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800 tracking-tight">Sistem TU</span>
                </a>
            </div>

            <!-- Content -->
            <div class="w-full sm:max-w-md px-4 sm:px-6 py-6 sm:py-8 bg-white shadow-lg sm:shadow-xl shadow-gray-200/50 overflow-hidden rounded-xl sm:rounded-2xl border border-gray-100">
                {{ $slot }}
            </div>
            
            <!-- Footer -->
            <div class="mt-4 sm:mt-6 text-center text-xs sm:text-sm text-gray-400">
                &copy; {{ date('Y') }} Sistem Manajemen Sekolah
            </div>
        </div>
    </body>
</html>