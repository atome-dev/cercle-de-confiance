<x-layouts::auth :title="__('Vérification en deux étapes')">
    <h1>Vérification en deux étapes</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <p>Merci de saisir le code généré par votre application d'authentification.</p>

    <form method="POST" action="{{ route('two-factor.login.store') }}">
        @csrf
        <input type="text" name="code" placeholder="Code à 6 chiffres" autofocus>
        <button type="submit">Se connecter</button>
    </form>

    <p>Ou utilisez un code de récupération :</p>

    <form method="POST" action="{{ route('two-factor.login.store') }}">
        @csrf
        <input type="text" name="recovery_code" placeholder="Code de récupération">
        <button type="submit">Se connecter avec un code de récupération</button>
    </form>
</x-layouts::auth>
