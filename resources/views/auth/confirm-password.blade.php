<x-layouts::auth :title="__('2FA')">
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <label for="password">{{ __('Mot de passe') }}</label>
        <input id="password" type="password" name="password" required autofocus>

        @error('password')
        <p>{{ $message }}</p>
        @enderror

        <button type="submit">{{ __('Confirmer') }}</button>
    </form>
</x-layouts::auth>
