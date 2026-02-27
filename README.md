# LM-Code SimpleSchedule

Application de planification moderne (evenements + taches) en PHP 8 et MySQL

<img width="1910" height="934" alt="image" src="https://github.com/user-attachments/assets/29dcc75c-98ae-402c-aba4-47a22b963881" />

## Objectifs du projet

- separer strictement frontend et backend
- centraliser la logique metier dans des services applicatifs
- isoler l acces base de donnees dans des repositories
- fournir des migrations + seeds versionnes
- proposer une UI responsive, moderne et coherente

## Stack technique

- PHP 8.3+ / 8.4+
- MySQL 8+
- Bootstrap 5.3 + Bootstrap Icons
- Chart.js 4
- FullCalendar 6
- Docker (optionnel)

## Architecture

```text
src/
  Domain/
    Entity/
    Repository/
  Application/
    DTO/
    Service/
  Infrastructure/
    Database/
    Repository/
  Shared/

api/                  # endpoints JSON
includes/             # layout frontend (header/footer)
database/migrations/  # migrations SQL versionnees
bootstrap/app.php     # composition des dependances
bin/migrate.php       # runner de migrations
```

## Guide developpeur (Michael - LM-Code)

Cette section sert de point d'entree rapide pour maintenir et faire evoluer le projet.

### Classes a connaitre en priorite

- `src/Application/Service/EventService.php`
  - use-case central create/update/delete/toggle task
  - valide le payload metier avant persistance
- `src/Application/Service/TagService.php`
  - use-case CRUD tags + stats d'usage
- `src/Application/Service/DashboardService.php`
  - compose les donnees de `index.php`
- `src/Application/Service/StatsService.php`
  - compose les donnees de `stats.php`
- `src/Infrastructure/Repository/PdoEventRepository.php`
  - requetes SQL evenement, transaction save, pivot `event_tags`
- `src/Infrastructure/Repository/PdoTagRepository.php`
  - requetes SQL tag + usage stats
- `bootstrap/app.php`
  - wiring DI (services/repositories)

### Fonctions utilitaires importantes

- `src/Shared/helpers.php::e()`
  - echappement HTML standard pour toutes les vues
- `src/Shared/helpers.php::csrf_token()`
  - generation/lecture du token CSRF session
- `src/Shared/helpers.php::csrf_verify()`
  - verification CSRF avant mutation

### Regle de maintenance

- modifier la logique metier dans `src/Application/Service/*`
- modifier SQL uniquement dans `src/Infrastructure/Repository/*`
- ne pas injecter de SQL dans les pages `*.php`
- ajouter une migration pour tout changement schema/seed
- mettre a jour README + tutoreil si une convention change

## Installation sans Docker (WampServer ou local natif)

### 1. Prerequis

- Apache + MySQL actifs
- PHP CLI disponible dans le PATH

### 2. Configurer la connexion DB

Par defaut, le projet lit `config/database.php` avec fallback:

- `DB_HOST` (defaut: `127.0.0.1`)
- `DB_PORT` (defaut: `3306`)
- `DB_NAME` (defaut: `simpleschedule`)
- `DB_USER` (defaut: `root`)
- `DB_PASS` (defaut: vide)
- `APP_TIMEZONE` (defaut: `Europe/Paris`)

### 3. Initialiser schema + seeds

```bash
php bin/migrate.php
```

### 4. Ouvrir l application

```text
http://localhost/simpleschedule/
```

## Installation avec Docker

### 1. Lancer les conteneurs

```bash
docker compose up -d --build
```

### 2. Executer les migrations dans le conteneur app

```bash
docker compose exec app php bin/migrate.php
```

### 3. Ouvrir l application

```text
http://localhost:8080/
```

- MySQL expose sur `localhost:3307`
- login root: `root`
- base: `simpleschedule`

## Seeds inclus

Le projet contient des donnees initiales:

- 10 tags
- 10 parametres applicatifs
- 10 evenements/taches de test
- liaisons `event_tags`

Migrations concernees:

- `001_create_core_tables.sql`
- `002_seed_default_tags.sql`
- `003_create_settings_table.sql`
- `004_seed_test_data.sql`
- `005_backfill_tags_catalog.sql`

## Endpoints API

- `GET /api/get-events.php?start=YYYY-MM-DD&end=YYYY-MM-DD`
- `GET /api/search.php?q=motcle`
- `POST /api/update-task.php`
- `POST|GET /api/delete-event.php`
- `GET /api/urgent.php?hours=4`

## Qualite et conventions

- aucune requete SQL dans les vues
- DTO pour transporter les donnees d entree
- validation metier dans les services
- repository interfaces en Domain
- implementation PDO en Infrastructure
- commentaires/docblocks concentres sur le "pourquoi" et la responsabilite

# migrer la base
php bin/migrate.php
```
