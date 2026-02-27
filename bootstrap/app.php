<?php

declare(strict_types=1);

use App\Application\Service\DashboardService;
use App\Application\Service\EventService;
use App\Application\Service\StatsService;
use App\Application\Service\TagService;
use App\Domain\Repository\EventRepositoryInterface;
use App\Domain\Repository\TagRepositoryInterface;
use App\Infrastructure\Database\ConnectionFactory;
use App\Infrastructure\Repository\PdoEventRepository;
use App\Infrastructure\Repository\PdoTagRepository;
use App\Shared\Container;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../src/Shared/Autoloader.php';
require_once __DIR__ . '/../src/Shared/helpers.php';

$config = require __DIR__ . '/../config/database.php';

date_default_timezone_set((string) ($config['app']['timezone'] ?? 'UTC'));

$container = new Container();

$container->set('config', static fn (): array => $config);
$container->set('pdo', static fn (): \PDO => ConnectionFactory::make($config['db']));
$container->set(EventRepositoryInterface::class, static fn (Container $c): EventRepositoryInterface => new PdoEventRepository($c->get('pdo')));
$container->set(TagRepositoryInterface::class, static fn (Container $c): TagRepositoryInterface => new PdoTagRepository($c->get('pdo')));
$container->set(TagService::class, static fn (Container $c): TagService => new TagService($c->get(TagRepositoryInterface::class)));
$container->set(EventService::class, static fn (Container $c): EventService => new EventService(
    $c->get(EventRepositoryInterface::class),
    $c->get(TagRepositoryInterface::class)
));
$container->set(DashboardService::class, static fn (Container $c): DashboardService => new DashboardService(
    $c->get(EventService::class),
    $c->get(TagService::class)
));
$container->set(StatsService::class, static fn (Container $c): StatsService => new StatsService(
    $c->get(EventRepositoryInterface::class),
    $c->get(TagService::class)
));

return $container;
