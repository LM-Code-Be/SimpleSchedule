<?php

declare(strict_types=1);

$container = require __DIR__ . '/bootstrap/app.php';

$pageTitle = 'Paramètres';
require __DIR__ . '/includes/header.php';
?>

<div class="row g-4">
    <div class="col-lg-7">
        <section class="panel-card">
            <div class="panel-header">
                <h1 class="h5 mb-0"><i class="bi bi-gear me-2"></i>Préférences locales</h1>
            </div>
            <form class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Thème UI</label>
                    <select class="form-select">
                        <option>Clair moderne</option>
                        <option>Contraste élevé</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fuseau horaire</label>
                    <select class="form-select">
                        <?php foreach (DateTimeZone::listIdentifiers() as $tz): ?>
                            <option value="<?= e($tz) ?>" <?= $tz === 'Europe/Paris' ? 'selected' : '' ?>><?= e($tz) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="button"><i class="bi bi-save me-1"></i>Enregistrer (bientôt)</button>
                </div>
            </form>
        </section>
    </div>

    <div class="col-lg-5">
        <section class="panel-card">
            <h2 class="h5"><i class="bi bi-shield-check me-2"></i>Qualité technique</h2>
            <ul class="mb-0 text-body-secondary">
                <li>Clean architecture appliquée</li>
                <li>API backend séparée</li>
                <li>Migrations SQL versionnées</li>
                <li>Service container + repositories</li>
            </ul>
        </section>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
