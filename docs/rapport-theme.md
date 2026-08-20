# Rapport — Gate d'accès public (code d'accès partagé)

Date : 2026-08-03

## Contexte

Mise en place d'un « rideau » public devant les pages non authentifiées (accueil, et futures charte/membres/contact) en attendant la vraie authentification Fortify. Un code d'accès partagé, affiché physiquement dans l'établissement, débloque le site via un cookie signé de 30 jours.

## Fichiers créés

### `config/access.php`

Nouvelle clé de configuration `code`, lue depuis `ACCESS_CODE`.

```php
return [
    'code' => env('ACCESS_CODE'),
];
```

### `app/Http/Middleware/EnsureAccessCodeIsValid.php`

Laisse passer les utilisateurs authentifiés (`auth()->check()`). Sinon, vérifie le cookie `access_granted` par comparaison en temps constant (`hash_equals`) et redirige vers `access.show` si absent/invalide.

```php
public function handle(Request $request, Closure $next): Response
{
    if (auth()->check()) {
        return $next($request);
    }

    $cookie = $request->cookie('access_granted');

    if (! is_string($cookie) || ! hash_equals(static::expectedCookieValue(), $cookie)) {
        return redirect()->route('access.show');
    }

    return $next($request);
}

public static function expectedCookieValue(): string
{
    return hash_hmac('sha256', config('access.code'), config('app.key'));
}
```

`expectedCookieValue()` est publique et statique pour être réutilisée telle quelle par le composant Livewire (pose du cookie) et par les tests (calcul du hash attendu), sans dupliquer la logique de signature.

### `resources/views/pages/access-gate.blade.php`

Composant Livewire SFC (`pages::access-gate`), layout `layouts::auth` (même habillage visuel que login/register). Un seul champ `code`, rate limiting manuel façon Fortify (6 tentatives / 60s par IP), pose du cookie 30 jours puis redirection vers l'accueil.

```php
new #[Layout('layouts::auth')] #[Title('Code d\'accès')] class extends Component {
    public string $code = '';

    public function attempt(): void
    {
        $this->validate(['code' => ['required', 'string']]);

        if (RateLimiter::tooManyAttempts($this->throttleKey(), 6)) {
            $this->addError('code', __('Trop de tentatives. Réessayez dans :seconds secondes.', [
                'seconds' => RateLimiter::availableIn($this->throttleKey()),
            ]));
            return;
        }

        if (! hash_equals((string) config('access.code'), $this->code)) {
            RateLimiter::hit($this->throttleKey(), 60);
            $this->addError('code', __('Code d\'accès incorrect.'));
            return;
        }

        RateLimiter::clear($this->throttleKey());

        Cookie::queue(
            'access_granted',
            EnsureAccessCodeIsValid::expectedCookieValue(),
            60 * 24 * 30,
            null, null,
            config('session.secure'),
            true, false,
            config('session.same_site', 'lax'),
        );

        $this->redirectRoute('home', navigate: true);
    }

    protected function throttleKey(): string
    {
        return 'access-code:'.request()->ip();
    }
}
```

⚠️ **Point d'attention technique** : les actions Livewire (`wire:submit`) passent par l'endpoint interne `/livewire/update`, pas par la route `/acces` — un middleware `throttle` sur la route n'aurait donc pas protégé le formulaire. Le rate limiting est reproduit à la main dans le composant (pattern `Laravel\Fortify\LoginRateLimiter`), sans nouvelle config dans un service provider.

Vue associée (fragment, single root element imposé par Livewire) :

```blade
<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Accès protégé')" :description="__('Saisissez le code d\'accès communiqué dans l\'établissement.')" />

    <form wire:submit="attempt" class="flex flex-col gap-6">
        <div>
            <flux:input wire:model="code" :label="__('Code d\'accès')" type="password" required autofocus autocomplete="off" />
            <flux:error name="code" />
        </div>

        <flux:button variant="primary" type="submit" class="w-full">{{ __('Valider') }}</flux:button>
    </form>
</div>
```

### `tests/Feature/AccessGateTest.php`

7 tests couvrant : redirection guest sans cookie, bon code → cookie posé + redirection, mauvais code → erreur sans cookie, cookie valide → accès direct, cookie invalide/falsifié → redirection, utilisateur authentifié → bypass, rate limiting après 6 tentatives échouées.

## Fichiers modifiés

- **`.env` / `.env.example`** — ajout de `ACCESS_CODE=changeme` avec commentaire (« code affiché physiquement dans l'établissement »).
- **`bootstrap/app.php`** — alias de middleware `access.code` :
  ```php
  $middleware->alias([
      'access.code' => EnsureAccessCodeIsValid::class,
  ]);
  ```
- **`routes/web.php`** — route `access.show` (non protégée, sinon boucle infinie) et middleware appliqué sur `home` :
  ```php
  Route::view('/', 'welcome')->name('home')->middleware('access.code');
  Route::livewire('/acces', 'pages::access-gate')->name('access.show');
  ```
- **`tests/Feature/ExampleTest.php`** — adapté pour passer un cookie d'accès valide, car `/` est désormais protégée (l'ancien test recevait un 302 au lieu du 200 attendu). Seule modification apportée à un test préexistant.

## Fichiers non modifiés

- `resources/css/app.css` et le thème déjà en place — non touchés, comme demandé.
- Aucun nouveau dossier racine créé.

## Commandes exécutées

```bash
php artisan test --compact tests/Feature/AccessGateTest.php
```
```json
{"tool":"pest","result":"passed","tests":7,"passed":7,"assertions":39,"duration_ms":434}
```

```bash
php artisan test --compact
```
```json
{"tool":"pest","result":"passed","tests":36,"passed":35,"assertions":104,"duration_ms":1493,"skipped":1,"risky":1}
```
(1 skipped et 1 risky préexistants, sans rapport avec ce changement — 2FA Fortify désactivé.)

```bash
vendor/bin/pint --dirty --format agent
```
```json
{"tool":"pint","result":"fixed","files":[{"path":"tests/Feature/ExampleTest.php","fixers":["single_blank_line_at_eof"]}]}
```

Re-lancement de la suite complète après le fix Pint :
```json
{"tool":"pest","result":"passed","tests":36,"passed":35,"assertions":104,"duration_ms":1547,"skipped":1,"risky":1}
```

Aucun build front (`npm run build`) n'a été nécessaire pour cette tâche : aucun fichier CSS/JS n'a été modifié.

## Suite possible

- Appliquer le middleware `access.code` aux futures routes charte/membres/contact dès leur création.
- Ajouter un lien « code oublié / contacter l'établissement » sur la page `/acces` si besoin UX.
- Revoir la stratégie de rotation du code : changer `ACCESS_CODE` invalide automatiquement tous les cookies déjà posés (comportement voulu, à documenter pour l'équipe).
