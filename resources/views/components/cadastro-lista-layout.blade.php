@props(['containerId', 'titulo', 'subtitulo'])

<div class="bg-gray-100 min-h-screen" id="{{ $containerId }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="space-y-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">{{ $titulo }}</h1>
                    <p class="mt-1 max-w-2xl text-xs text-gray-500">{{ $subtitulo }}</p>
                </div>

                <div class="grid w-full grid-cols-2 gap-2 sm:w-auto sm:flex sm:items-center sm:justify-end">
                    {{ $acoes }}
                </div>
            </div>

            {{ $slot }}
        </div>
    </div>
</div>
