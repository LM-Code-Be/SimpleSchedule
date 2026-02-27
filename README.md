# LM-Code SimpleSchedule

Application de planification moderne (evenements + taches) en PHP 8, structuree selon la **Clean Architecture**.

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

## Commandes utiles

```bash
# verifier la syntaxe PHP
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }

# migrer la base
php bin/migrate.php
```

## Notes

- `structure.md` est volontairement ignore par git (documentation locale interne).
- `tutoreil.md` detaille le developpement du projet de zero.
