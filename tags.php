<?php

declare(strict_types=1);

use App\Application\Service\TagService;

$container = require __DIR__ . '/bootstrap/app.php';
$tagService = $container->get(TagService::class);

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify($_POST['_csrf'] ?? null)) {
        throw new RuntimeException('Session expirée.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_tag'])) {
        $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
        $tagService->save($id, (string) $_POST['name'], (string) $_POST['color']);
        header('Location: tags.php?saved=1');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_tag'])) {
        $tagService->delete((int) $_POST['id']);
        header('Location: tags.php?deleted=1');
        exit;
    }
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

$tags = $tagService->list();
$usage = $tagService->usageStats();
$editingTag = $editId ? $tagService->get($editId) : null;

$pageTitle = 'Tags';
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-tags me-2"></i>Gestion des tags</h1>
        <p class="text-body-secondary mb-0">Catégorisez vos événements avec des tags colorés.</p>
    </div>
</div>

<?php if (!empty($_GET['saved'])): ?>
    <div class="alert alert-success">Tag enregistré.</div>
<?php elseif (!empty($_GET['deleted'])): ?>
    <div class="alert alert-warning">Tag supprimé.</div>
<?php endif; ?>

<?php if (isset($errorMessage)): ?>
    <div class="alert alert-danger"><?= e($errorMessage) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <section class="panel-card sticky-form">
            <div class="panel-header">
                <h2 class="h5 mb-0"><i class="bi bi-tag me-2"></i><?= $editingTag ? 'Modifier' : 'Créer' ?> un tag</h2>
            </div>
            <form method="post" class="vstack gap-3">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="save_tag" value="1">
                <?php if ($editingTag): ?>
                    <input type="hidden" name="id" value="<?= (int) $editingTag->id ?>">
                <?php endif; ?>
                <div>
                    <label class="form-label">Nom</label>
                    <input class="form-control" name="name" required value="<?= e($editingTag?->name ?? '') ?>">
                </div>
                <div>
                    <label class="form-label">Couleur</label>
                    <input class="form-control form-control-color" type="color" name="color" value="<?= e($editingTag?->color ?? '#2463eb') ?>">
                </div>
                <button class="btn btn-primary" type="submit"><i class="bi bi-save me-1"></i>Enregistrer</button>
                <?php if ($editingTag): ?>
                    <a class="btn btn-outline-secondary" href="tags.php">Annuler</a>
                <?php endif; ?>
            </form>
        </section>
    </div>

    <div class="col-lg-8">
        <section class="panel-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Tag</th>
                        <th>Couleur</th>
                        <th>Utilisation</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($usage === []): ?>
                        <tr><td colspan="4" class="text-center text-body-secondary py-4">Aucun tag.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($usage as $row): ?>
                        <tr>
                            <td><span class="tag-chip" style="--tag-color: <?= e((string) $row['color']) ?>"><?= e((string) $row['name']) ?></span></td>
                            <td><code><?= e((string) $row['color']) ?></code></td>
                            <td><?= (int) $row['usage_count'] ?> événement(s)</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="tags.php?edit=<?= (int) $row['id'] ?>"><i class="bi bi-pencil"></i></a>
                                <form method="post" class="d-inline" onsubmit="return confirm('Supprimer ce tag ?');">
                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="delete_tag" value="1">
                                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
