# Règles métier et conventions du projet

Ce répertoire contient les règles de domaine à fournir à Laravel Boost selon les fichiers que l'agent est en train de consulter.

## Fonctionnement du mapping

Le système de règles utilise ce fichier comme index : chaque **glob** (motif de chemin) est associé à un ou plusieurs fichiers Markdown de règles. Lorsqu'un fichier correspond à un motif, l'agent doit charger les règles indiquées avant de proposer ou de modifier du code.

- Les motifs sont évalués sur les chemins du projet, par exemple `app/**` ou `tests/**`.
- Les chemins des règles sont relatifs à `.ai/rules/`.
- Une règle plus spécifique peut compléter une règle plus générale.
- Garder les règles centrées sur le domaine, les invariants et les décisions durables ; éviter d'y recopier du code ou de la documentation technique déjà disponible ailleurs.
- Mettre à jour ce mapping lorsqu'un nouveau module ou une nouvelle zone fonctionnelle nécessite des règles dédiées.

## Mapping de démarrage

| Glob de fichiers concernés | Fichier(s) de règles à charger | Intention |
|---|---|---|
| `app/**` | `overview.md`, `modules.md`, `business-rules.md` | Comprendre le produit, ses modules et ses invariants métier avant de modifier le code applicatif. |
| `app/Livewire/**` | `overview.md`, `modules.md`, `business-rules.md` | Appliquer les règles métier aux composants Livewire 4 classiques. |
| `resources/views/livewire/**` | `overview.md`, `modules.md` | Relier les vues Livewire aux modules et aux parcours métier. |
| `routes/**` | `overview.md`, `modules.md`, `business-rules.md` | Vérifier les parcours exposés et les contraintes métier associées. |
| `tests/**` | `overview.md`, `modules.md`, `business-rules.md` | Écrire des tests Pest cohérents avec les scénarios et règles métier documentés. |
| `database/**` | `overview.md`, `modules.md`, `business-rules.md` | Préserver les invariants métier dans les migrations, modèles et seeders. |
| `app/Models/Thread.php`, `app/Actions/CreateThreadWithMessage.php`, `app/Actions/ShareThread.php`, `app/Models/ThreadKeyGrant.php` | `models.md` | Modèle d'accès par grant (`ThreadKeyGrant`) plutôt que par rôle — voir la règle enregistrée avant de toucher à l'accès aux dossiers. |

## Relation avec les guidelines Pest Browser

Le fichier `.ai/guidelines/pest-browser.blade.php` reste utilisé par Laravel Boost séparément. **Ne le dupliquez pas dans ce répertoire et ne le supprimez pas.** Les règles ci-dessus décrivent le domaine ; la guideline Pest Browser conserve ses propres conventions pour les tests de navigateur.

Les tests doivent rester compatibles avec Pest et, lorsque cela s'applique, avec les conventions de test navigateur déjà définies dans `.ai/guidelines/pest-browser.blade.php`.
