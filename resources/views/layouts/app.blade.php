<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config("app.name", "Pixel Studio") }}</title>
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    @include('partials.pixel-loader')
<div class="flex min-h-screen">
    <aside class="w-56 bg-gray-900 text-gray-100 p-4 space-y-1">
        <div class="font-bold text-lg mb-4">Pixel Studio</div>
        @foreach ([
            "dashboard" => "Dashboard", "sales.index" => "Sales", "customers.index" => "Customers",
            "products.index" => "Products", "suppliers.index" => "Suppliers", "expenses.index" => "Expenses",
            "reports.profit" => "Reports",
        ] as $route => $label)
            <a href="{{ route($route) }}" class="block px-3 py-2 rounded hover:bg-gray-800">{{ $label }}</a>
        @endforeach
    </aside>
    <main class="flex-1 p-6">
        @if (session("success"))
            <div class="mb-4 bg-green-100 text-green-800 px-4 py-2 rounded">{{ session("success") }}</div>
        @endif
        {{ $slot }}
    </main>
</div>
</body>
</html>
