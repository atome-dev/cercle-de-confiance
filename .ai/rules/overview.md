# Vue d'ensemble du projet — Cercle de confiance

Ce fichier décrit le domaine métier, le périmètre fonctionnel et la stack
technique retenue pour l'application **Cercle de confiance**. Il sert de
référence transverse ; les règles métier détaillées sont dans
`business-rules.md` et l'inventaire des pages est dans `modules.md`.

---

## Contexte métier

L'application héberge un **cercle de confiance** destiné à une école :
un espace fermé où les membres (élèves, familles, personnels) peuvent
soumettre des témoignages ou signalements de manière anonyme, et où des
administrateurs désignés (équipe de confiance, référents harcèlement,
cellule d'écoute) peuvent prendre en charge ces dossiers.

La promesse produit tient en trois points :

1. **Anonymat technique réel** des expéditeurs — pas seulement une
   promesse d'affichage, mais une impossibilité d'identification par
   l'équipe technique sans coopération active de l'administrateur.
2. **Confiance durable** — les échanges chiffrés ne peuvent être lus
   que par les personnes explicitement mandatées, même si la base de
   données fuit.
3. **Traçabilité opérationnelle** — chaque dossier suit un cycle de vie
   traçable (nouveau → en cours → archivé) avec notifications des
   messages non lus.

## Objectif du site

Permettre à toute personne autorisée à franchir le **gate d'accès par
code école** de :

- consulter la charte du cercle et la liste publique des membres ;
- déposer un témoignage anonyme via le formulaire « Nous contacter » ;
- revenir suivre l'avancement de son dossier grâce à un code de suivi
  personnel `ABCD-EFGH` ;
- laisser les membres connectés consulter la liste des dossiers qui
  leur sont attribués et dialoguer avec les expéditeurs ;
- laisser les administrateurs gérer contenus, membres et statuts.

## Stack technique

Stack confirmée pour l'implémentation (versions minimales imposées par
le cahier des charges) :

| Couche | Paquet | Version minimale |
|---|---|---|
| Langage | PHP | `^8.3` |
| Framework | `laravel/framework` | `^13.17` |
| Composants interactifs | `livewire/livewire` | `^4.1` |
| UI kit | `livewire/flux` | `^2.15` |
| UI kit Pro | `livewire/flux-pro` | `^2.15` |
| Authentification | `laravel/fortify` | `^1.37` |
| Rôles & permissions | `spatie/laravel-permission` | `^8.3` |
| Internationalisation | `spatie/laravel-translatable` | `^6.14` |
| Optimisation Blade | `livewire/blaze` | `^1.0` |
| Tests | `pestphp/pest` | `^4.7` |
| Tests navigateur | `pestphp/pest-plugin-browser` | `^4.3` |
| Tests Laravel | `pestphp/pest-plugin-laravel` | `^4.1` |

Conventions dérivées de la stack :

- **Livewire 4.1 — composants classiques uniquement.** Pas de Single
  File Component (SFC) ; les composants restent des classes PHP
  couplées à des vues Blade. Cela reste cohérent avec l'usage de
  Pest Browser et facilite l'audit du code métier (les classes
  Livewire sont le lieu principal où appliquer les règles
  d'anonymat et de chiffrement).
- **Flux / Flux Pro** fournit tous les composants d'interface
  (formulaires, modales, tables, navigation). Tailwind CSS reste le
  moteur de style sous-jacent ; pas de framework CSS alternatif.
- **Fortify** est utilisé en mode headless : il expose les routes et
  contrôleurs d'authentification (login, logout, password reset) sans
  ses vues, qui sont remplacées par les composants Flux.
- **spatie/laravel-permission** est l'unique source de vérité pour
  les rôles applicatifs — voir `business-rules.md` section *Rôles*.
- **spatie/laravel-translatable** gère l'i18n FR/EN — voir la décision
  explicite dans `business-rules.md` section *Internationalisation*.
- **livewire/blaze** est activé pour l'optimisation du rendu Blade ;
  son usage est **transparent** (activation en provider, pas de
  modification du code applicatif). Aucune règle de code n'en est
  tirée.
- **Pest 4.7** + plugins `browser` et `laravel` constituent la stack
  de test unique. Pas de PHPUnit parallèle, pas de Cypress /
  Playwright additionnel.

> **Note de versionnage — cohérence avec Laravel Boost.** Le projet
> utilise déjà Laravel Boost (MCP) pour l'assistance au
> développement. Les fichiers de règles `.ai/rules/` ne documentent
> pas exhaustivement l'API de chaque paquet. **Avant d'utiliser une
> méthode, un helper ou une façade d'un paquet qui n'est pas
> explicitement mentionné dans `overview.md`, `modules.md` ou
> `business-rules.md`, vérifier la version installée via
> `composer show <vendor>/<package>`** et croiser avec la
> documentation officielle. Cela évite les régressions silencieuses
> lors d'un `composer update`.

## Rôles applicatifs

Trois rôles, gérés intégralement par `spatie/laravel-permission` :

| Rôle (slug exact) | Label | Capacités |
|---|---|---|
| `parent` | Parent | Reçoit automatiquement l'accès à tout dossier groupé à sa création ; consulter les dossiers pour lesquels il détient un accès (grant) ; répondre à un expéditeur ; partager un dossier avec d'autres utilisateurs. |
| `professeur` | Professeur | Mêmes capacités qu'un `parent` sur les dossiers auxquels il a accès, mais n'obtient **jamais** d'accès automatique à la création d'un dossier groupé — uniquement via partage ou en tant que destinataire direct. |
| `administrateur` | Administrateur | Gérer les contenus (charte, présentation des membres), gérer les comptes et rôles. **Aucun accès automatique aux dossiers** — doit être explicitement partagé, comme un professeur (voir `business-rules.md`, section *Partage de dossier*). |

L'accès à un dossier **précis** n'est jamais décidé par le rôle seul
: il dépend d'un enregistrement explicite (« grant ») propre à
chaque utilisateur, détaillé dans `business-rules.md`.

**Aucun autre rôle** ne doit être créé sans mise à jour explicite de
`business-rules.md`. Les utilisateurs sans rôle attribué ne peuvent
accéder qu'aux pages publiques (gate, accueil, charte, présentation
des membres, formulaire de contact, page i18n) — toute autre route
redirige vers la page de connexion.

## Glossaire métier

| Terme | Définition |
|---|---|
| **Cercle de confiance** | L'espace fermé et chiffré de l'école pour les signalements anonymes. |
| **Gate d'accès** | Page d'entrée protégée par le code école (ex. `ABYZ`). |
| **Code de suivi** | Code `ABCD-EFGH` remis à l'expéditeur pour suivre son dossier. |
| **Dossier** | Unité de traitement d'un signalement, avec son cycle de vie. |
| **Statut** | État d'un dossier : `nouveau`, `en cours`, `archivé`. |
| **Enveloppe anon** | Couche de chiffrement protégeant l'identité réelle de l'expéditeur. |
| **Code de service** | Secret partagé entre administrateurs habilités, utilisé pour dériver la clé de l'enveloppe anon. |

## Documents associés

- `business-rules.md` — règles métier détaillées (rôles, chiffrement,
  statuts, i18n).
- `modules.md` — inventaire des 12 pages/fonctionnalités.
- `.ai/rules/index.md` — point d'entrée du système de règles du
  projet (voir `cahier-des-charges-rules.md`).
