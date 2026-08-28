<x-layouts::auth :title="__('2FA')">
    <h1>Double authentification obligatoire</h1>

@if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

@if (! auth()->user()->two_factor_secret)
    <form method="POST" action="/user/two-factor-authentication">
        @csrf
        <button type="submit">Activer le 2FA</button>
    </form>
@else
    <div id="qr-code">
        {!! auth()->user()->twoFactorQrCodeSvg() !!}
    </div>

    <form method="POST" action="/user/confirmed-two-factor-authentication">
        @csrf
        <input type="text" name="code" placeholder="Code à 6 chiffres" required>
        <button type="submit">Confirmer</button>
    </form>
@endif
</x-layouts::auth>
