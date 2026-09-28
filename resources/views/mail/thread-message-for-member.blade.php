<x-mail::message>
# Nouveau message

Un nouveau message a été déposé sur le dossier **{{ $thread->code }}**.

Pour des raisons de confidentialité, son contenu n'est pas reproduit dans ce courriel : connectez-vous pour le consulter.

<x-mail::button :url="$url">
Ouvrir le dossier
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
