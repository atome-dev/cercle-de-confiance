# Modules et pages — Cercle de confiance

Ce fichier inventorie les **12 pages / fonctionnalités** de
l'application. Pour chaque page on précise : rôle requis, route,
but fonctionnel, et règles métier notables. Les règles transverses
(chiffrement, rôles, i18n) restent dans `business-rules.md`.

---

## 1. Gate d'accès (code école)

- **Route :** `/` (avant tout autre contenu)
- **Visiteurs :** publics non encore passés.
- **But :** filtrer l'accès à l'application par la saisie du **code
  école** (ex. `ABYZ`). Sans ce code, le reste du site n'est pas
  accessible (même les pages publiques ne sont pas servies).
- **Règles métier notables :**
    - Le code est saisi une fois par session ; sa validation pose un
      marqueur de session `gate_passed_at` horodaté.
    - En cas de code incorrect, message générique (pas d'indice sur la
      longueur ou le format).
    - Le code est défini via variable d'environnement (`APP_GATE_CODE`)
      et comparé en temps constant (`hash_equals`) pour éviter tout
      canal latéral.
    - Voir `business-rules.md` section *Gate d'accès (code école)*.

## 2. Page d'accueil

- **Route :** `/accueil` (après passage du gate)
- **Visiteurs :** tout visiteur passé le gate.
- **But :** présenter le Cercle et orienter vers les sections clés.
- **Contenu :**
    - Titre de présentation du Cercle.
    - Résumé court de la charte (1 paragraphe) avec lien vers la page
      « Notre charte ».
    - Bouton d'accès au formulaire « Nous contacter ».
    - Lien vers la page « Notre charte ».
    - Lien vers la page « Nous contacter ».

## 3. Page « Notre charte »

- **Route :** `/charte`
- **Visiteurs :** tout visiteur passé le gate.
- **But :** présenter la charte d'engagement du Cercle (règles de
  fonctionnement, engagements de confidentialité, périmètre des
  signalements).
- **Règles métier notables :**
    - Contenu **éditable par un administrateur** depuis l'espace
      d'administration (voir page 10).
    - Affichée en FR et en EN via `spatie/laravel-translatable` (voir
      `business-rules.md` section *Internationalisation*).

## 3bis. Page « Protection des données personnelles » (RGPD)

- **Route :** `/rgpd` — **sans** middleware `access.code`, contrairement
  au reste des pages publiques : une notice de confidentialité doit
  rester consultable sans barrière.
- **Visiteurs :** absolument tout le monde, y compris avant saisie du
  code d'accès.
- **But :** informer sur les données collectées (nom/email facultatifs
  du formulaire de contact, comptes membres, contenu des dossiers,
  cookies), leur protection (chiffrement par dossier), et les droits
  RGPD (accès, rectification, effacement, opposition, limitation,
  portabilité, réclamation CNIL).
- **Règles métier notables :**
    - Contenu statique (`resources/views/pages/rgpd.blade.php`),
      **pas** éditable depuis l'administration, contrairement à la
      charte.
    - Identité du responsable de traitement (association LIBRE ÉCOLE
      RUDOLF STEINER), contact RGPD (`cercledeconfiancevlb@gmail.com`)
      et hébergeur (OVH) sont renseignés. Il reste à définir les
      **durées de conservation** (dossiers archivés, comptes de
      membres partis) — un encart d'avertissement le rappelle sur la
      page elle-même.
    - Lien présent dans le pied de page de `layouts::public`, pas dans
      la navigation principale (convention usuelle pour ce type de
      page).

## 4. Page de présentation des membres

- **Route :** `/membres`
- **Visiteurs :** tout visiteur passé le gate.
- **But :** présenter publiquement la composition du Cercle :
  membres de l'équipe de confiance avec leur rôle fonctionnel
  (référent harcèlement, cellule d'écoute, etc.).
- **Règles métier notables :**
    - **Aucune donnée nominative sensible** n'est exposée ici — les
      membres sont identifiés par leur rôle fonctionnel, pas par leur
      identité civile complète.
    - Les photos éventuelles sont stockées en dehors de la base
      applicative si elles permettent une identification.
    - Contenu éditable par un administrateur (voir page 10).

## 5. Formulaire « Nous contacter »

- **Route :** `/contact`
- **Visiteurs :** tout visiteur passé le gate.
- **But :** permettre à un visiteur de soumettre un témoignage ou
  signalement de manière anonyme.
- **Champs :**
    - Champ de message obligatoire.
    - Pas de champ identifiant l'expéditeur — l'anonymat est une
      **garantie technique**, pas une case à cocher.
- **À la soumission :**
    - création d'un dossier (statut initial `nouveau`) ;
    - génération d'un **code de suivi `ABCD-EFGH`** remis à
      l'expéditeur ;
    - application des trois niveaux de chiffrement (voir
      `business-rules.md` section *Chiffrement*).

## 6. Accès au suivi de dossier (expéditeur)

- **Route :** `/suivi`
- **Visiteurs :** publics non identifiés (par design, c'est le
  pendant de l'anonymat).
- **But :** permettre à l'expéditeur de retrouver son dossier et
  lire les messages échangés avec le membre en charge.
- **Mécanisme :**
    - Formulaire de saisie du code `ABCD-EFGH`.
    - Le code, combiné à un sel applicatif, dérive la clé de
      l'enveloppe app de l'expéditeur (voir `business-rules.md`).
    - L'expéditeur ne peut **que consulter** — il ne peut ni changer
      le statut, ni voir les autres dossiers, ni contacter
      l'administration.

## 7. Connexion membre / administrateur

- **Route :** `/connexion`
- **Visiteurs :** utilisateurs avec identifiant (personnel de
  l'école / équipe de confiance).
- **But :** authentifier un parent, un professeur ou un administrateur.
- **Mécanisme :**
    - Basé sur `laravel/fortify` (login + logout + password reset).
    - À la connexion, le rôle (`parent`, `professeur` ou
      `administrateur`) est lu via `spatie/laravel-permission` et
      conditionne l'accès aux routes protégées (voir
      `business-rules.md`).

## 8. Espace membre — liste des dossiers

- **Route :** `/membre/dossiers`
- **Rôle requis :** `parent`, `professeur` ou `administrateur`
  (accès à la page ; le contenu réel dépend des grants, voir
  ci-dessous).
- **But :** présenter à l'utilisateur connecté la liste des dossiers
  auxquels il a accès.
- **Contenu :**
    - Liste des dossiers visibles par l'utilisateur connecté :
      identifiant anonyme (court, non signifiant), **nom de
      l'expéditeur** (« Anonyme » si non renseigné — voir
      `business-rules.md` section *Formulaire de contact et notion
      d'anonymat*), destinataire, section et classe assignées (tiret
      si non classé — voir `business-rules.md` section *Sections et
      classes*), statut courant, date de dernier message, indicateur
      de messages non lus.
    - Interrupteur « Afficher les dossiers archivés » (`ThreadsList::$showArchived`,
      `flux:switch`) — masqué par défaut, pas un filtre par statut
      détaillé (`nouveau`/`en cours` restent toujours visibles).
- **Règles métier notables :**
    - Un utilisateur ne voit **que** les dossiers pour lesquels il
      détient un grant (voir `business-rules.md`, section *Partage
      de dossier*), jamais la liste globale.

## 9. Espace membre — détail d'un dossier

- **Route :** `/membre/dossiers/{dossier}`
- **Rôle requis :** aucun rôle spécifique — détenir un grant sur ce
  dossier précis (voir `business-rules.md`).
- **But :** consulter le détail d'un dossier et dialoguer avec
  l'expéditeur.
- **Contenu :**
    - Messages échangés, **déchiffrés à la volée** (clé en mémoire
      seulement) ; l'expéditeur (accès anonyme) voit « Vous » sur ses
      propres messages, un membre voit « Expéditeur ».
    - Zone de réponse (envoi chiffré), avec un choix « Expéditeur »
      / « Interne » réservé aux membres grantés — un message
      « Interne » n'est jamais visible par l'expéditeur ; voir
      `business-rules.md` section *Réponses internes (messages du
      dossier)*.
    - Bouton de changement de statut (`en cours` / `archivé`) —
      uniquement par un titulaire d'un grant sur ce dossier.
    - Attribution d'une section et/ou d'une classe (listes
      parent-enfant, section → classes de cette section) —
      uniquement par un titulaire d'un grant sur ce dossier ; voir
      `business-rules.md` section *Sections et classes*.
    - Zone de commentaire interne — réservée aux titulaires d'un
      grant ayant le rôle `parent` ou `professeur` (un
      `administrateur` granté ne la voit pas) ; voir
      `business-rules.md` section *Commentaire interne (dossier)*.
- **Règles métier notables :**
    - Le déchiffrement se fait dans le contrôleur / composant
      Livewire juste avant l'affichage ; la version en clair n'est
      jamais persistée ni loguée.
    - Toute action est tracée (audit log minimal : qui, quand, quoi).

## 10. Espace administrateur — gestion des contenus

- **Route :** `/admin`
- **Rôle requis :** `administrateur`.
- **But :** permettre à un administrateur de :
    - gérer les contenus publics (charte, présentation des membres) ;
    - gérer les comptes (parents, professeurs, administrateurs) et
      leur rôle ;
    - partager un dossier avec un parent ou un professeur ;
    - forcer un changement de statut ;
    - **déchiffrer l'enveloppe anon** d'un dossier via le **code de
      service** (secret partagé des administrateurs habilités), pour
      les cas dûment justifiés.
- **Règles métier notables :**
    - L'usage du code de service est tracé (qui, quand, pour quel
      dossier).
    - La clé dérivée n'est jamais loguée ni exposée à l'UI — elle
      vit en mémoire uniquement le temps de l'opération.
    - **Suivi des connexions** (page `/membres`, `App\Livewire\Membres`) :
      chaque connexion réussie est enregistrée dans `user_logins`
      (écouteur de l'événement `Login` dans `AppServiceProvider`), y
      compris via « Se souvenir de moi » ; une connexion en attente du
      code de double authentification n'est comptée qu'une fois le code
      validé. La table des membres affiche le nombre de connexions, la
      première et la dernière (« Jamais » à défaut).
    - Seule la **date-heure** est conservée — ni adresse IP, ni
      navigateur — et l'historique est supprimé avec le compte (clé
      étrangère en cascade). Cette collecte est mentionnée sur `/rgpd` ;
      tout ajout de donnée (IP, user-agent…) doit y être reporté.
    - Voir `business-rules.md` sections *Rôles* et *Chiffrement*.

## 11. Internationalisation (FR/EN)

- **Route :** n/a (middleware de locale + sélecteur de langue).
- **Visiteurs :** tout visiteur passé le gate.
- **But :** servir le contenu public (charte, présentation des
  membres, libellés d'interface) en français et en anglais.
- **Mécanisme :**
    - **Choix explicite :** `spatie/laravel-translatable` est utilisé
      en mode **attributs castés en `translatable`** sur les modèles
      concernés (Charte, Membre). **Aucune table de traductions
      séparée** n'est utilisée.
    - Sélecteur de langue stocké en session + cookie.
    - Locale par défaut : `fr`.
- **Règles métier notables :**
    - Seuls les champs explicitement marqués translatable sont
      concernés ; les champs techniques (statuts, rôles, codes) ne
      sont **pas** traduits.
    - Voir `business-rules.md` section *Internationalisation* pour la
      justification détaillée de l'approche et la liste exhaustive des
      attributs translatables.

## 12. Réunions (agenda et disponibilités)

- **Route :** `/reunions` (`App\Livewire\Meetings`), avec trois onglets
  sélectionnés par le paramètre `?onglet=` : `calendrier` (défaut),
  `disponibilites`, `creneaux`. Le paramètre `?semaine=` (lundi de la
  semaine, `Y-m-d`) est partagé par les deux derniers onglets.
- **Rôle requis :** `parent`, `professeur` ou `administrateur`.
- **But :** planifier les réunions du Cercle et trouver le moment qui
  réunit le plus de membres.
- **Onglet « Calendrier » :** calendrier mensuel des réunions
  (`Meeting`), prochaine réunion mise en avant, création / édition /
  suppression par tout utilisateur ayant l'un des trois rôles.
- **Onglet « Mes disponibilités »** (`App\Livewire\MeetingAvailabilities`) :
  grille hebdomadaire personnelle que le membre « peint » (clic, ou
  glisser à la souris) avec le mode choisi : présentiel, distanciel ou
  indisponible.
- **Onglet « Trouver un créneau »** (`App\Livewire\MeetingSlotFinder`) :
  croise les disponibilités de la semaine selon trois filtres (durée,
  nombre minimum de personnes, nombre minimum en présentiel) ; affiche
  les meilleurs créneaux et une carte de chaleur, et le bouton
  « Planifier » ouvre le formulaire de réunion prérempli (événement
  `plan-meeting` écouté par `Meetings::planMeeting()`).
- **Règles métier notables :**
    - Seuls les `parent` et `professeur` peuvent être participants
      d'une réunion (`User::meetingMembers()`) ; ce sont donc les seuls
      à déclarer des disponibilités et à être comptés par la recherche
      de créneau. Un `administrateur` sans l'un de ces rôles consulte
      la recherche mais ne saisit rien.
    - Créneaux de **30 minutes, de 8 h à 22 h**
      (`MeetingAvailability::slotTimes()`). Une ligne
      `meeting_availabilities` par membre et par créneau, avec un mode
      `presentiel` ou `distanciel` (`App\Enums\AvailabilityMode`) ;
      **l'absence de ligne signifie « indisponible »**.
    - Les créneaux passés ne sont plus modifiables ni proposés ; les
      créneaux hors grille ou mal formés envoyés au serveur sont ignorés
      silencieusement.
    - Un membre ne compte pour un créneau que s'il est disponible sur
      **toute** la durée de la réunion ; il ne compte « en présentiel »
      que s'il l'est sur **chaque** demi-heure, sinon il compte à
      distance.
    - Les meilleurs créneaux (5 au plus) sont classés par nombre de
      personnes, puis nombre en présentiel, puis date, sans
      chevauchement entre eux.

---

## Cohérence avec les autres fichiers de règles

- Toute règle de **chiffrement** mentionnée ici est détaillée dans
  `business-rules.md` (enveloppe app, enveloppe anon, contenu
  message).
- Toute règle de **rôles / permissions** est détaillée dans
  `business-rules.md`.
- Les **versions minimales** des paquets utilisés sur chaque page
  sont rappelées dans `overview.md` (à vérifier via
  `composer show <vendor>/<package>`).
