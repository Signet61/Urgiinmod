<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js',])
</head>

<body class="font-sans text-gray-900 antialiased">

    <script src="js/webPet.min.js"></script>
    <script>
        window.WebPetLang = {
            hello: "Сайн уу",
            welcome: "Тавтай морил",
            touch: "Яагаад дарна?",
        };
        new WebPet({
            name: "cat",
            action: { randomMove: true }
        });
    </script>

    <div class="min-h-screen w-full flex flex-col justify-center items-center bg-cover bg-center bg-no-repeat p-4"
        style="background-image: url('{{ asset('landscape.png') }}');">

        <div class="mb-6">
            <a href="/">
                <img src="mongoliandeer.png" class="w-32 h-32 md:w-48 md:h-48 object-contain"
                    alt="Deer Animation">
            </a>
        </div>

        <div
            class="w-full sm:max-w-md px-8 py-10 bg-white/80 backdrop-blur-md shadow-2xl rounded-3xl border border-white/20">
            {{ $slot }}
        </div>

    </div>
</body>

</html>