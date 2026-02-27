# Tutoreil - Developper LM-Code SimpleSchedule de zero

Ce document explique comment reconstruire le projet pas a pas, puis l executer en local sans Docker et avec Docker.

## 1. Vision produit

Objectif: construire une application de planning moderne avec:

- evenements calendaires
- taches et suivi done/not-done
- tags/categories
- statistiques visuelles
- export JSON/ICS

Contraintes de qualite:

- backend separe du frontend
- logique metier testable et centralisee
- SQL uniquement dans la couche Infrastructure
- migrations versionnees

## 2. Concevoir l architecture

On adopte la Clean Architecture:

- Domain: entites + interfaces repository
- Application: services (use-cases) + DTO
- Infrastructure: PDO + SQL concret
- Presentation: pages PHP et endpoints API

Arborescence cible:

```text
src/Domain
src/Application
src/Infrastructure
src/Shared
api
includes
database/migrations
bootstrap
bin
```

## 3. Modeliser la base de donnees

Tables principales:

- `events`
- `tags`
- `event_tags`
- `app_settings`
- `schema_migrations` (technique)

Regles cle:

- relation many-to-many entre `events` et `tags`
- colonnes `priority` et `status`
- index sur dates et colonnes de filtre

## 4. Implementer le backend

### 4.1 Domain

- creer `Event` et `Tag`
- definir `EventRepositoryInterface` et `TagRepositoryInterface`

### 4.2 Application

- creer `EventPayload` et `EventFilters`
- implementer `EventService`, `TagService`, `DashboardService`, `StatsService`

### 4.3 Infrastructure

- creer `ConnectionFactory` (PDO)
- implementer `PdoEventRepository` et `PdoTagRepository`

### 4.4 Bootstrap

- autoload (`src/Shared/Autoloader.php`)
- container DI (`src/Shared/Container.php`)
- composition des services dans `bootstrap/app.php`

## 5. Construire le frontend

Pages principales:

- `index.php` dashboard
- `events.php` CRUD principal
- `calendar.php` vue calendrier
- `tasks.php`, `tags.php`, `stats.php`

UI:

- style global dans `assets/css/style.css`
- charts centralises dans `assets/js/script.js`
- menu actif fort contraste (couleur + fond + bordure)
- responsive mobile first

## 6. Construire les API

Endpoints en JSON dans `api/` qui appellent les services Application:

- get events
- search
- update task
- delete event
- urgent events

## 7. Migrations et seeds

Creer les migrations SQL versionnees:

1. schema de base
2. seed tags
3. settings
4. seed test data
5. backfill du catalogue tags (compatibilite upgrades)

Runner: `bin/migrate.php`

- cree la base si absente
- applique les scripts non executes
- enregistre l historique

## 8. Lancer le projet sans Docker (Wamp/local)

### Prerequis

- PHP CLI 8.3+
- MySQL 8+
- Apache actif

### Etapes

1. Placer le dossier dans `www` (Wamp).
2. Configurer les variables d environnement DB si necessaire.
3. Migrer:

```bash
php bin/migrate.php
```

4. Ouvrir:

```text
http://localhost/simpleschedule/
```

## 9. Lancer le projet avec Docker

### Prerequis

- Docker Desktop
- Docker Compose

### Etapes

1. Build et demarrage:

```bash
docker compose up -d --build
```

2. Migrations:

```bash
docker compose exec app php bin/migrate.php
```

3. Ouvrir l app:

```text
http://localhost:8080/
```

DB Docker:

- host local: `127.0.0.1`
- port: `3307`
- user: `root`
- pass: `root`
- db: `simpleschedule`

## 10. Workflow de developpement recommande

1. creer branche feature
2. coder dans la couche cible (Domain/Application/Infrastructure/Presentation)
3. verifier `php -l`
4. relancer migrations si besoin
5. tester pages + API
6. commit clair en francais

## 11. Checklist qualite avant push

- [ ] pas de SQL dans les vues
- [ ] tous les formulaires sensibles proteges CSRF
- [ ] pages responsives desktop/mobile
- [ ] migration ajoutee pour toute evolution schema
- [ ] README mis a jour
- [ ] seed coherents pour demo

## 12. Evolutions conseillees

- authentification + roles
- tests unitaires (services)
- tests d integration (repositories)
- CI (lint + tests + migration dry-run)
- i18n FR/EN
