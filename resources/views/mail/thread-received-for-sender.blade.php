<x-mail::message>
# Nous avons bien reçu votre message

Merci de nous avoir fait confiance. Votre message a bien été transmis au Cercle de confiance, qui reviendra vers vous dès que possible.

Pour suivre votre dossier et lire nos réponses, rendez-vous sur la page de suivi et saisissez le code de suivi qui vous a été remis lors de votre envoi.

<x-mail::panel>
Ce code n'est conservé nulle part et ne figure pas dans ce courriel, afin de préserver votre anonymat : en cas de perte, nous ne pourrons pas vous le renvoyer.
</x-mail::panel>

<x-mail::button :url="$url">
Suivre mon dossier
</x-mail::button>

Si vous n'êtes pas à l'origine de ce message, vous pouvez ignorer ce courriel.

{{ config('app.name') }}
</x-mail::message>
