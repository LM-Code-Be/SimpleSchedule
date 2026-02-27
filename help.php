<?php

declare(strict_types=1);

$container = require __DIR__ . '/bootstrap/app.php';

$pageTitle = 'Aide';
require __DIR__ . '/includes/header.php';
?>

<section class="panel-card">
    <div class="panel-header">
        <h1 class="h5 mb-0"><i class="bi bi-question-circle me-2"></i>Aide rapide</h1>
    </div>
    <div class="accordion" id="helpAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#h1">
                    Où créer un événement ?
                </button>
            </h2>
            <div id="h1" class="accordion-collapse collapse show" data-bs-parent="#helpAccordion">
                <div class="accordion-body">Allez sur <strong>Événements</strong>, bouton <strong>Créer</strong>, puis enregistrez.</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#h2">
                    Comment séparer frontend et backend ?
                </button>
            </h2>
            <div id="h2" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                <div class="accordion-body">Le frontend est dans les pages/vues, le backend dans `src/` + `api/`. Les vues n’exécutent pas de SQL.</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#h3">
                    Comment initialiser la base ?
                </button>
            </h2>
            <div id="h3" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                <div class="accordion-body">Lancez `php bin/migrate.php` depuis le dossier du projet.</div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
