<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('app.name') }} - École Steiner Waldorf
</x-mail::header>
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
<x-mail::footer>
Ce courriel vous a été envoyé automatiquement par le {{ config('app.name') }}, merci de ne pas y répondre.<br>
<a href="{{ route('rgpd.show') }}">Protection des données personnelles</a>
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
