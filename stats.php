<?php

declare(strict_types=1);

use App\Application\Service\StatsService;

$container = require __DIR__ . '/bootstrap/app.php';
$stats = $container->get(StatsService::class)->data();

$weekly = $stats['weekly'];
$monthly = $stats['monthly'];
$priority = $stats['priority'];
$tags = $stats['tags'];

$weeklyMap = [];
foreach ($weekly as $row) {
    $weeklyMap[(string) $row['day_key']] = (int) $row['total'];
}

$weeklyLabels = [];
$weeklyValues = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime('-' . $i . ' days'));
    $weeklyLabels[] = date('d/m', strtotime($date));
    $weeklyValues[] = $weeklyMap[$date] ?? 0;
}

$monthlyLabels = array_map(static fn (array $row): string => date('M Y', strtotime((string) $row['month_key'] . '-01')), $monthly);
$monthlyValues = array_map(static fn (array $row): int => (int) $row['total'], $monthly);

$priorityMap = ['low' => 0, 'normal' => 0, 'high' => 0];
foreach ($priority as $row) {
    $priorityMap[(string) $row['priority']] = (int) $row['total'];
}

$tagMap = [];
foreach ($tags as $row) {
    $name = trim((string) $row['name']);
    if ($name === '') {
        continue;
    }
    $key = strtolower($name);
    $tagMap[$key] ??= [
        'name' => $name,
        'usage_count' => 0,
        'color' => (string) $row['color'],
    ];
    $tagMap[$key]['usage_count'] += (int) $row['usage_count'];
}

uasort($tagMap, static fn (array $a, array $b): int => $b['usage_count'] <=> $a['usage_count']);
$tagTop = array_slice(array_values($tagMap), 0, 8);
$tagLabels = array_map(static fn (array $row): string => (string) $row['name'], $tagTop);
$tagValues = array_map(static fn (array $row): int => (int) $row['usage_count'], $tagTop);
$tagColors = array_map(static fn (array $row): string => (string) $row['color'], $tagTop);

$totalEvents = array_sum($weeklyValues);
$totalMonthly = array_sum($monthlyValues);
$totalTagsUsed = count(array_filter($tagValues, static fn (int $v): bool => $v > 0));
$totalHighPriority = $priorityMap['high'];

$pageTitle = 'Statistiques';
require __DIR__ . '/includes/header.php';
?>

<section class="hero-card mb-4">
    <div>
        <p class="eyebrow mb-2">Analyse</p>
        <h1 class="h3 mb-1">Statistiques avancées</h1>
        <p class="mb-0 opacity-75">Vue claire de la charge, des priorités et des catégories pour piloter votre planning.</p>
    </div>
</section>

<section class="stats-highlight mb-4">
    <article class="metric-card">
        <div class="metric-icon bg-primary-subtle text-primary"><i class="bi bi-calendar-week"></i></div>
        <div>
            <div class="metric-label">Volume 7 jours</div>
            <div class="metric-value"><?= (int) $totalEvents ?></div>
        </div>
    </article>
    <article class="metric-card">
        <div class="metric-icon bg-info-subtle text-info"><i class="bi bi-bar-chart"></i></div>
        <div>
            <div class="metric-label">Volume mensuel cumulé</div>
            <div class="metric-value"><?= (int) $totalMonthly ?></div>
        </div>
    </article>
    <article class="metric-card">
        <div class="metric-icon bg-danger-subtle text-danger"><i class="bi bi-exclamation-triangle"></i></div>
        <div>
            <div class="metric-label">Priorité haute</div>
            <div class="metric-value"><?= (int) $totalHighPriority ?></div>
        </div>
    </article>
    <article class="metric-card">
        <div class="metric-icon bg-success-subtle text-success"><i class="bi bi-tags"></i></div>
        <div>
            <div class="metric-label">Tags réellement utilisés</div>
            <div class="metric-value"><?= (int) $totalTagsUsed ?></div>
        </div>
    </article>
</section>

<div class="row g-4">
    <div class="col-xl-8">
        <section class="panel-card stats-chart mb-4">
            <div class="panel-header">
                <h2 class="h5 mb-0">Charge sur 7 jours</h2>
            </div>
            <div class="chart-wrap">
                <canvas id="weeklyChart"></canvas>
            </div>
        </section>

        <section class="panel-card stats-chart">
            <div class="panel-header">
                <h2 class="h5 mb-0">Tendance mensuelle</h2>
            </div>
            <div class="chart-wrap">
                <canvas id="monthlyChart"></canvas>
            </div>
        </section>
    </div>

    <div class="col-xl-4">
        <section class="panel-card stats-chart mb-4">
            <div class="panel-header">
                <h2 class="h5 mb-0">Répartition des priorités</h2>
            </div>
            <div class="chart-wrap chart-wrap-sm">
                <canvas id="priorityChart"></canvas>
            </div>
        </section>

        <section class="panel-card stats-chart">
            <div class="panel-header">
                <h2 class="h5 mb-0">Usage des tags</h2>
            </div>
            <div class="chart-wrap chart-wrap-sm">
                <canvas id="tagsChart"></canvas>
            </div>
        </section>
    </div>
</div>

<script>
window.appCharts = {
    weekly: {
        labels: <?= json_encode($weeklyLabels, JSON_THROW_ON_ERROR) ?>,
        values: <?= json_encode($weeklyValues, JSON_THROW_ON_ERROR) ?>
    },
    monthly: {
        labels: <?= json_encode($monthlyLabels, JSON_THROW_ON_ERROR) ?>,
        values: <?= json_encode($monthlyValues, JSON_THROW_ON_ERROR) ?>
    },
    priority: {
        labels: ['Basse', 'Normale', 'Haute'],
        values: <?= json_encode([$priorityMap['low'], $priorityMap['normal'], $priorityMap['high']], JSON_THROW_ON_ERROR) ?>
    },
    tags: {
        labels: <?= json_encode($tagLabels, JSON_THROW_ON_ERROR) ?>,
        values: <?= json_encode($tagValues, JSON_THROW_ON_ERROR) ?>,
        colors: <?= json_encode($tagColors, JSON_THROW_ON_ERROR) ?>
    }
};
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
