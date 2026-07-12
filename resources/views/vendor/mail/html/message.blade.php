@props(['logo' => null])
<x-mail::layout>
{{-- Header — a logo vem da view raiz (único ponto que enxerga $message p/ o CID). --}}
<x-slot:header>
<x-mail::header :url="config('app.url')" :logo="$logo" />
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer />
</x-slot:footer>
</x-mail::layout>
