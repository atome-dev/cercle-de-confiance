# Règles métier et invariants — Cercle de confiance

Ce fichier consigne les règles **non-évidentes** du projet : celles
qu'on ne devine pas en lisant le code, qui ont été tranchées par le
cahier des charges et qui doivent être respectées par toute
contribution. Les pages concernées sont dans `modules.md`, la stack
est dans `overview.md`.

---

## Gate d'accès (code école)

- Le code est stocké en variable d'environnement `APP_GATE_CODE` ; il
  n'est jamais committé, jamais logué.
- La comparaison utilise `hash_equals()` pour neutraliser les canaux
  latéraux de type *timing attack*.
- Le passage du gate pose un marqueur de session `gate_passed_at`
  horodaté. Toute route (y compris les pages publiques) exige ce
  marqueur — un visiteur qui n'a pas passé le gate ne reçoit qu'une
  302 vers `/`.
- Le message d'erreur en cas de code incorrect est générique : pas
  d'indice sur la longueur, le format, ou la proximité avec un code
  valide.

## Rôles et permissions

**Décision tranchée :** les rôles applicatifs sont gérés
intégralement par `spatie/laravel-permission` (`^8.3`). Trois rôles
existent dans le système :

| Slug (DB) | Label | Capacités |
|---|---|---|
| `parent` | Parent | Accès aux dossiers pour lesquels il détient un grant (voir section *Partage de dossier*), réponse à un expéditeur, partage du dossier avec d'autres utilisateurs. |
| `professeur` | Professeur | Mêmes capacités que `parent`, mais n'obtient jamais de grant automatique à la création d'un dossier groupé — uniquement via partage ou en tant que destinataire direct. |
| `administrateur` | Administrateur | Gestion des contenus, gestion des comptes/rôles. N'a **pas** d'accès automatique aux dossiers (voir *Partage de dossier*) ; doit être explicitement partagé comme un `professeur`. |

Le rôle `membre` a été **retiré** (remplacé par `parent`/`professeur`)
car il ne permettait pas de distinguer qui reçoit un dossier groupé
par défaut de qui doit y être explicitement invité.

**Aucun autre rôle ne doit être ajouté** sans mise à jour explicite
de ce fichier et de `overview.md`. Les utilisateurs sans rôle
attribué ne peuvent accéder qu'aux pages publiques listées dans
`modules.md` ; toute autre route renvoie vers la page de connexion.

**Conventions d'application :**

- Les rôles sont attachés à un `User` (modèle Laravel standard) via
  la relation `roles` exposée par `spatie/laravel-permission`.
- La vérification côté UI se fait via `@role('parent')` /
  `@role('professeur')` / `@role('administrateur')` (directives
  Blade du paquet).
- La vérification côté code applicatif se fait via
  `$user->hasRole('administrateur')` ou
  `$user->can('...')` si une permission nommée est définie.
- **L'accès à un dossier précis n'est jamais décidé par le rôle** :
  il dépend uniquement de l'existence d'un grant (voir *Partage de
  dossier*). Le rôle ne détermine que qui peut recevoir un grant
  automatique à la création, et qui peut accéder à la page
  `/dossiers` en général.
- Les permissions fines (ex. `contenu.edit`) sont optionnelles ; on
  s'appuie principalement sur le rôle, sauf quand une action doit
  être tracée séparément.

## Formulaire de contact et notion d'anonymat

**Décision tranchée (revue) :** le nom et l'email de l'expéditeur
sont **optionnels**, jamais requis (`ContactForm`, pas de règle
`required`) — la case « Je préfère pour le moment rester anonyme »
n'a qu'un rôle de réassurance dans l'UI, elle ne change **aucun**
comportement de stockage (`sendAnonymously` n'est lu nulle part).

**Interrupteur `ANONYMOUS_CONTACT_ENABLED` (depuis 2026-09-28)** —
`config('access.anonymous_contact_enabled')`, `true` par défaut. À
`false` (choix actuel de l'établissement), le nom et l'email
deviennent **obligatoires** (`ContactForm::rules()`) et la case
« rester anonyme » est masquée. Rien d'autre ne change : code de
suivi, chiffrement et accès `/mon-dossier` restent identiques, de
sorte que les messages anonymes puissent être réactivés sans
migration.

- **`sender_name`** et **`sender_email`** sont chiffrés dans
  l'**enveloppe app** (`ThreadEncryptionService::sealTextForApp()` /
  `Crypt`), donc **lisibles par l'application et les membres grantés
  en tout temps**, sans code de suivi — voir
  `Thread::decryptedSenderName()` / `decryptedSenderEmail()`.
    - Liste des dossiers (`ThreadsList`) : colonne « Expéditeur »
      (entre « Code » et « Destinataire »), nom seul, « Anonyme »
      si le champ est resté vide.
    - Détail d'un dossier (`ThreadShow`) : nom **et** email affichés
      sous le numéro de dossier — **réservé aux membres grantés**
      (même garde `isAccessibleBy` que la classification/le
      partage) ; l'expéditeur consultant son propre dossier via le
      code de suivi ne voit jamais cette ligne (il connaît déjà ses
      propres coordonnées).
- Ni le nom ni l'email ne sont donc protégés par le code de suivi —
  seul le **contenu des messages** et la **clé du dossier pour
  l'accès anonyme** (`anon_key_envelope`) restent dans l'enveloppe
  anon, dérivée du code de suivi (voir *Chiffrement*).
- Le **code de suivi `ABCD-EFGH`** reste le seul élément remis à
  l'expéditeur pour accéder à son dossier et lire les réponses ; il
  ne conditionne plus la lecture du nom (voir section *Chiffrement*).
- L'application ne conserve **aucune** information de corrélation
  (IP, user-agent, session ID) entre la soumission du formulaire et
  l'accès à la page de suivi.

## Dossiers et statuts

- Un dossier est créé **uniquement** à la soumission d'un formulaire
  « Nous contacter » ; il n'existe pas de création manuelle.
- Statuts possibles : `nouveau`, `en cours`, `archivé`.
- Transitions autorisées :
    - `nouveau` → `en cours` (premier message d'un membre / admin) ;
    - `en cours` → `archivé` (décision explicite) ;
    - `archivé` → `en cours` (réouverture justifiée par
      l'administrateur — tracée).
- Le passage à `archivé` n'efface pas le dossier : il le retire des
  listes actives et bloque l'envoi de nouveaux messages par
  l'expéditeur (réponse consultable uniquement).

## Purge RGPD des dossiers archivés

**Implémenté** (`App\Console\Commands\PurgeArchivedThreads`,
planifiée quotidiennement dans `routes/console.php`) : un dossier
passé au statut `archive` est **définitivement supprimé** (dossier,
messages, notes internes, grants — cascade au niveau des clés
étrangères `onDelete('cascade')`) `PurgeArchivedThreads::RETENTION_MONTHS`
(6) mois après sa clôture. Voir `/rgpd`, section « Combien de temps
vos données sont conservées ».

- `threads.archived_at` (nullable) marque le moment du dernier
  passage à `archive` — c'est le point de départ du délai, distinct
  de `updated_at` qui bouge pour toute autre modification du
  dossier. `ThreadShow::updateStatus()` le renseigne à `now()` en
  passant à `archive`, et le remet à `null` en repassant à
  `en_cours` : **rouvrir un dossier annule la purge programmée**,
  même après plusieurs mois d'archivage.
- Le texte de `/rgpd` doit rester synchronisé avec
  `PurgeArchivedThreads::RETENTION_MONTHS` si cette durée change (un
  test le vérifie : `tests/Feature/RgpdPageTest.php`).
- La suppression des **comptes de membres** qui quittent
  l'établissement, elle, n'est **pas automatisée** — reste à la
  discrétion du responsable du traitement (voir `/rgpd`).

## Sections et classes

**Décision tranchée :** les sections et classes de l'école sont des
**constantes applicatives** (`App\Enums\Section`, `App\Enums\SchoolClass`)
et non des tables de référence en base — pas de CRUD, pas de
formulaire d'administration pour les créer/renommer. Seule
l'**association** à un dossier est persistée, via deux colonnes
nullable sur `threads` : `section` et `school_class`.

| Section | Classes |
|---|---|
| Jardin d'enfants | JE Parc, JE Pommier |
| Élémentaire | 1ère à 5ème classe |
| Collège | 6ème à 9ème classe |
| Lycée | 10ème à 12ème classe |

- Un dossier peut être associé à une **section**, à une **classe**,
  aux deux, ou à aucune des deux — les deux champs sont nullable en
  base. Dans l'UI (`App\Livewire\ThreadShow`), les deux listes sont
  synchronisées : choisir une classe positionne automatiquement la
  section correspondante (`SchoolClass::section()`) ; choisir une
  section réinitialise la classe sur « Aucune classe ». Il reste donc
  possible de n'assigner qu'une section, mais pas d'enregistrer une
  classe incohérente avec la section affichée.
- L'attribution se fait depuis la page « Détail d'un dossier »
  (`App\Livewire\ThreadShow::updateClassification()`), par n'importe
  quel titulaire d'un grant sur ce dossier — même règle
  d'autorisation que le changement de statut (voir *Partage de
  dossier*).
- La liste des dossiers affiche la section et la classe assignées
  (colonnes entre « Destinataire » et « Statut »), avec un tiret
  (« — ») quand le dossier n'est pas encore classé.
- Toute évolution de la liste des sections/classes (ajout,
  renommage) se fait en éditant les enums `App\Enums\Section` /
  `App\Enums\SchoolClass` ; aucune migration de table de référence
  n'est nécessaire — seules les colonnes `threads.section` /
  `threads.school_class` sont en base.

## Commentaire interne (dossier)

**Décision tranchée :** un dossier porte un commentaire libre
(texte, optionnel) réservé à un usage interne — visible et
modifiable depuis la page « Détail d'un dossier »
(`App\Livewire\ThreadShow::updateComment()`).

- **Chiffré comme les messages échangés**, pas comme le nom/l'email
  de l'expéditeur : `threads.comment_ciphertext` / `comment_iv` /
  `comment_tag`, AES-256-GCM avec la **clé du dossier** (même
  primitive que `ThreadMessage` — voir *Chiffrement*, `ThreadEncryptionService::encryptMessage()` /
  `Thread::encryptComment()` / `decryptComment()`). Contrairement au
  nom/email (enveloppe app, lisibles par l'application seule), le
  commentaire n'est déchiffrable que par un titulaire d'un
  `ThreadKeyGrant` sur ce dossier précis.
- **Restriction plus stricte que le reste de la page dossier :**
  contrairement au statut, à la classification et au partage
  (ouverts à tout titulaire d'un grant, y compris `administrateur`),
  le commentaire est réservé aux titulaires d'un grant ayant le rôle
  `parent` **ou** `professeur`. Un `administrateur` granté ne voit
  même pas le champ (`ThreadShow::canComment()` renvoie `false`).
- L'expéditeur (accès anonyme via le code de suivi) ne voit jamais ce
  champ — il n'est affiché que dans le bloc réservé aux membres
  authentifiés et grantés.
- Un seul commentaire par dossier (pas d'historique, pas d'auteur
  tracé) — à la différence des messages échangés avec l'expéditeur.
  Chaque enregistrement régénère un IV/tag aléatoires (comme un
  nouveau message), l'ancien ciphertext est simplement remplacé.

## Réponses internes (messages du dossier)

**Décision tranchée :** chaque message d'un dossier porte un booléen
`thread_messages.is_internal` (défaut `false`). À l'envoi d'une
réponse, un membre granté choisit entre deux destinations via des
boutons radio à côté du champ « Votre réponse » (`App\Livewire\ThreadShow`,
propriété `replyVisibility` : `sender` ou `internal`) :

- **« Expéditeur »** (défaut) : message normal, visible par
  l'expéditeur comme par tout membre granté — comportement inchangé.
- **« Interne »** : note visible **uniquement** par les membres
  grantés (tous rôles confondus, y compris `administrateur` —
  contrairement au commentaire ci-dessus qui exclut ce rôle) ;
  jamais visible par l'expéditeur.

Règles :

- Le choix n'est proposé qu'aux membres grantés
  (`ThreadShow::canReplyInternally()`) ; l'expéditeur (accès
  anonyme) ne voit même pas les boutons radio et ne peut jamais
  produire un message interne, y compris en altérant la requête
  (`reply()` recalcule `isInternal` côté serveur à partir du grant,
  jamais depuis la seule propriété cliente).
- Les messages internes sont exclus de `decryptedMessages()` pour
  l'expéditeur (`reject()` avant déchiffrement) — ils ne sont ni
  affichés, ni comptés pour lui.
- Un message interne reste chiffré exactement comme un message
  normal (même enveloppe de clé, même chiffrement AES-256-GCM) ; seul
  son affichage diffère.

## Partage de dossier

**Décision tranchée :** l'accès à un dossier n'est plus une simple
vérification de rôle mais repose sur une table `thread_key_grants` :
chaque utilisateur autorisé détient sa propre enveloppe chiffrée de
la clé du dossier (`ThreadKeyGrant`), révocable individuellement.

- **Dossier groupé** (« Cercle de Confiance ») : à la création, tous
  les utilisateurs ayant le rôle `parent` reçoivent automatiquement
  un grant. **Si aucun `parent` n'existe** à cet instant, les
  `administrateur` reçoivent le grant à sa place (garde-fou pour
  éviter qu'un dossier reste inaccessible à quiconque).
- **Dossier à destinataire direct** (formulaire « un membre en
  particulier ») : seul l'utilisateur choisi (parent ou professeur)
  reçoit le grant initial.
- **Partage :** n'importe quel titulaire d'un grant peut partager le
  dossier avec n'importe quel autre utilisateur (`App\Actions\ShareThread`),
  sans restriction de rôle sur qui partage ou qui peut recevoir. Le
  partage crée un nouveau grant scellé à partir de la clé déjà
  détenue par celui qui partage ; il ne modifie pas les grants
  existants (idempotent : partager avec quelqu'un déjà granté ne
  fait rien).
- **Révocation :** retirer un grant est individuel et explicite
  (suppression de la ligne `ThreadKeyGrant`) — **retirer le rôle
  `parent`/`professeur` à quelqu'un ne révoque pas rétroactivement**
  les dossiers auxquels il a déjà accès.
- L'administrateur n'a **aucun** accès automatique aux dossiers ; il
  doit être explicitement partagé, exactement comme un professeur.

## Notifications de messages non lus

**Implémenté** (`Thread::hasUnreadFor()`, affiché dans `ThreadsList` —
point bleu + code en gras sur la ligne concernée) : un dossier est
« non lu » pour un membre si le dernier message de l'autre partie
(l'expéditeur, ou tout autre membre) est postérieur à son dernier
passage (`ThreadRead.last_read_at`), ou s'il n'a jamais ouvert le
dossier.

- **Piège N+1 :** `hasUnreadFor()`/`lastReadAtFor()` interrogent
  `messages()`/`reads()` à chaque appel par défaut — correct pour un
  seul dossier (ex. `ThreadShow`) mais coûteux répété sur une liste.
  Les deux méthodes utilisent `relationLoaded()` pour réutiliser une
  relation déjà eager-chargée quand elle est disponible.
  `ThreadsList::threads()` eager-charge donc `reads` (filtrée sur
  l'utilisateur courant) et `messages` (colonnes minimales) en 2
  requêtes pour toute la page, quel que soit le nombre de dossiers
  affichés — ne pas repasser par une requête par dossier en modifiant
  ce code.
- L'indicateur est calculé en temps réel à l'ouverture de la page
  « Liste des dossiers » (pas de cron nécessaire — pas de
  notification push dans le périmètre actuel).
- **Notifications email (depuis 2026-09-28)** : chaque nouveau
  message (`App\Actions\NotifyThreadParticipants`, appelé par
  `CreateThreadWithMessage` et `ReplyToThread`) envoie un courriel
  **en file d'attente** (`ShouldQueueAfterCommit`) à toutes les
  personnes concernées sauf l'auteur : les membres grantés
  (`NewThreadMessageForMember`) et, si le message vient d'un membre,
  n'est pas interne et qu'une adresse a été laissée, l'expéditeur
  (`NewThreadMessageForSender`). À la création du dossier,
  l'expéditeur qui a laissé une adresse reçoit en plus une
  confirmation de réception (`ThreadReceivedForSender`, envoyée par
  `CreateThreadWithMessage`).
    - Un membre peut refuser ces courriels depuis son profil
      (« Recevoir les notifications », `users.receives_email_notifications`,
      activé par défaut). Le choix est vérifié **au moment de l'envoi**
      (`NewThreadMessageForMember::shouldSend()`), donc aussi pour les
      courriels déjà en file. L'indicateur « non lu » de l'application
      reste inchangé.
    - Les courriels ne contiennent **jamais** le contenu des messages
      ni le code de suivi — seulement un lien (dossier pour les
      membres, `/mon-dossier` pour l'expéditeur).
    - L'expéditeur est notifié via le `Thread` lui-même
      (`routeNotificationForMail()`) : son adresse n'est déchiffrée
      qu'au moment de l'envoi et ne figure jamais en clair dans la
      file (Redis).

## Accès expéditeur (code ABCD-EFGH)

- Le code est généré par `Str::random(...)` puis formaté en deux
  groupes de 4 caractères majuscules séparés par un tiret.
- Il est remis à l'expéditeur **une seule fois** lors de la
  confirmation de soumission.
- L'application ne stocke pas le code en clair ; seul son hash
  (Argon2) est conservé côté dossier. Le code est donc
  **non récupérable** par l'équipe technique — c'est le prix de
  l'anonymat.
- Cinq tentatives erronées sur une fenêtre glissante d'une heure
  déclenchent un blocage temporaire de l'accès `/suivi` pour l'IP
  concernée.
- **Décision confirmée (2026-09-16) :** `/dossiers/{thread}` est
  placée derrière le middleware `access.code` comme le reste du site
  public — un expéditeur anonyme doit donc connaître **à la fois**
  le code de l'établissement (cookie `access_granted`) **et** son
  code de suivi personnel `ABCD-EFGH` pour consulter son dossier. Ce
  n'est plus un point d'entrée exempté (voir `routes/web.php`, qui
  regroupe `home`, `charte.show`, `contact.show`, `anonymous-access`
  et `threads.show` sous un même groupe `access.code`). `/rgpd` et
  `/acces` restent les seules routes publiques non gatées.

---

## Chiffrement

L'application manipule trois familles de données sensibles :

1. **Nom de l'expéditeur** (`sender_name`) — lisible par l'app/les
   membres, voir *Formulaire de contact et notion d'anonymat*.
2. **Email de l'expéditeur** (`sender_email`) — protégé, non lisible
   sans le code de suivi.
3. **Contenu des messages** échangés entre l'expéditeur et le
   membre en charge.
4. **Identifiant applicatif** du dossier (le code de suivi et
   l'attribution).

Ces trois familles sont protégées par **trois couches
indépendantes** (chiffrement à double enveloppe + chiffrement du
contenu), détaillées ci-dessous.

### Principe à trois niveaux

| Niveau | Quoi ? | Comment ? | Pourquoi ? |
|---|---|---|---|
| **Enveloppe app** | Identifiant applicatif du dossier (code de suivi, attribution, statut) **et nom de l'expéditeur** (`sender_name`). | `Crypt` natif Laravel (`Illuminate\Support\Facades\Crypt`, AES-256-CBC + HMAC-SHA256). | Format standard, auditable, intégrable aux dumps et backups. Le nom est volontairement dans ce niveau (pas l'anon) car il doit rester lisible par les membres sans code de suivi. |
| **Enveloppe anon** | Email de l'expéditeur (`sender_email`) et clé du dossier pour l'accès anonyme. | `Encrypter` Laravel dédié, dérivé via **HKDF** à partir du code de suivi. | Permet de compartimenter : la clé de l'enveloppe anon est séparée de la clé de l'enveloppe app, et peut être détruite indépendamment. |
| **Contenu du message** | Texte des messages échangés. | `openssl_encrypt` en **AES-256-GCM** (avec IV aléatoire et tag d'authentification). | Le `Crypt` natif de Laravel ne supporte **pas** AES-GCM. Or GCM est requis pour offrir à la fois confidentialité **et** intégrité du contenu avec un seul primitive, et pour permettre la parallélisation côté lecture. La dérogation à `Crypt` est donc **documentée et justifiée** par ce besoin. |

### Conséquence fonctionnelle

- **Confidentialité** : un dump de la base **seule** (sans `APP_KEY`)
  ne révèle ni le nom, ni l'email des expéditeurs, ni le contenu des
  messages. Avec `APP_KEY` (ex. serveur compromis), le **nom** de
  l'expéditeur redevient lisible (enveloppe app) — l'**email**, lui,
  reste protégé car il exige en plus le code de suivi (enveloppe
  anon). Ce n'est donc plus un anonymat technique complet pour le
  nom, seulement pour l'email.
- **Intégrité** : toute altération d'un message est détectée à la
  lecture (tag GCM invalide → exception, pas d'affichage partiel).
- **Compartimentation** : la compromission d'une clé n'expose
  **pas** automatiquement les autres couches ; en particulier, la
  clé de l'enveloppe anon peut être détruite sans rendre le reste
  illisible.
- **Récupération** : la destruction de la clé anon rend l'**email**
  de l'expéditeur définitivement irrécupérable, même pour
  l'administrateur — mais pas son nom, lisible via l'enveloppe app.

### Conventions de code

- Toute écriture d'un champ sensible passe par un **helper
  applicatif** (`encryptApp()`, `encryptAnon()`, `encryptMessage()`)
  documenté dans le code — pas d'appel direct à `Crypt::encrypt` /
  `openssl_encrypt` dans les contrôleurs ou les composants
  Livewire.
- Toute lecture passe par le helper de déchiffrement correspondant
  ; la donnée en clair vit **uniquement en mémoire** le temps de
  l'affichage.
- Les clés sont lues depuis des variables d'environnement
  (`APP_KEY`, `APP_ANON_KEY`) ; aucune clé n'est committée, loguée,
  ni sérialisée.

---

## Internationalisation (FR/EN)

**Décision tranchée :** l'application utilise
`spatie/laravel-translatable` (`^6.14`) en mode **attributs castés
en `translatable`** sur les modèles concernés. **Aucune table de
traductions séparée** n'est mise en place.

### Pourquoi ce choix

- Le projet n'a que deux locales (`fr`, `en`) et un nombre limité
  de champs traduisibles (contenus éditables : charte, présentation
  des membres). Une table de traductions séparée serait
  disproportionnée.
- Le cast `translatable` stocke les traductions sous forme de JSON
  dans la colonne de l'attribut : simple à sauvegarder / migrer /
  exporter, et compatible avec le format natif Laravel.
- Cela évite de multiplier les jointures au runtime — utile pour
  conserver de bonnes performances sur les pages publiques
  (charte, présentation des membres).

### Modèles et attributs concernés

| Modèle | Attributs translatables |
|---|---|
| `Charte` | `titre`, `contenu` |
| `MembrePresentation` | `role_fonctionnel`, `bio_courte` |

Les champs suivants ne sont **pas** translatables : `statut`
(dossier), `slug` (rôles `spatie/laravel-permission`), codes
(`ABCD-EFGH`, code école).

### Conventions d'application

- Le cast est déclaré dans le modèle :
  `protected $casts = ['titre' => 'translatable', ...]`.
- L'accès en lecture passe par `$model->titre` (renvoie la valeur
  dans la locale courante) ; en écriture par
  `$model->setTranslation('titre', 'en', '...')`.
- La locale courante est gérée par un middleware qui lit le cookie
  / la session, avec fallback sur `config('app.locale')` (par
  défaut `fr`).
- Toute migration qui ajoute un nouveau champ traduisible doit :
    1. ajouter la colonne ;
    2. ajouter le cast `translatable` dans le modèle ;
    3. ajouter l'attribut à la liste ci-dessus dans ce fichier.

---

## Consignes de rédaction de ce fichier

- Chaque règle ajoutée doit préciser : **quoi**, **pourquoi**,
  **où elle s'applique** (route / modèle / composant), **depuis
  quand**.
- Aucune règle ne doit contredire `overview.md` (versions de
  paquets) ou `modules.md` (inventaire des pages) ; en cas de
  contradiction, mettre à jour les trois fichiers ensemble.
- Les dérogations (cas où une règle est transgressée) doivent être
  **explicitement justifiées** (ex. AES-GCM vs `Crypt`).
