<?php $pageTitle = 'Liste des séances - My Cinema'; ?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="mb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <i class="fas fa-calendar-alt text-blue-600"></i> Gestion des Séances
            </h1>
            <p class="text-gray-600">Planifiez et gérez vos séances</p>
        </div>
        <a href="index.php?controller=seance&action=ajouter" 
           class="btn-primary inline-flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Programmer une séance
        </a>
    </div>
</div>

<!-- Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-3"></i>
            <p><?= htmlspecialchars($_SESSION['success']) ?></p>
        </div>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-3"></i>
            <p><?= $_SESSION['error'] ?></p>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Filtres -->
<div class="card mb-6">
    <form method="GET" action="index.php" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="hidden" name="controller" value="seance">
        <input type="hidden" name="action" value="liste">
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
            <input type="date" name="date" 
                   value="<?= htmlspecialchars($_GET['date'] ?? '') ?>"
                   class="input-field">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Film</label>
            <select name="film_id" class="input-field">
                <option value="">Tous les films</option>
                <?php foreach ($films as $film): ?>
                    <option value="<?= $film['id'] ?>" <?= ($_GET['film_id'] ?? '') == $film['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($film['titre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Salle</label>
            <select name="salle_id" class="input-field">
                <option value="">Toutes les salles</option>
                <?php foreach ($salles as $salle): ?>
                    <option value="<?= $salle['id'] ?>" <?= ($_GET['salle_id'] ?? '') == $salle['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($salle['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="flex items-end">
            <button type="submit" class="btn-primary w-full">
                <i class="fas fa-filter mr-2"></i>Filtrer
            </button>
        </div>
    </form>
</div>

<!-- Liste des séances -->
<?php if (empty($seances)): ?>
    <div class="card text-center py-12">
        <i class="fas fa-calendar-times text-gray-300 text-6xl mb-4"></i>
        <p class="text-gray-500 text-lg">Aucune séance trouvée</p>
        <a href="index.php?controller=seance&action=ajouter" class="btn-primary mt-4 inline-block">
            Programmer votre première séance
        </a>
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php 
        $currentDate = null;
        foreach ($seances as $seance): 
            $seanceDate = date('Y-m-d', strtotime($seance['date']));
            if ($currentDate !== $seanceDate):
                $currentDate = $seanceDate;
                $dateLabel = date('l d F Y', strtotime($seance['date']));
                if ($seanceDate === date('Y-m-d')) {
                    $dateLabel = "Aujourd'hui - " . date('d F Y', strtotime($seance['date']));
                } elseif ($seanceDate === date('Y-m-d', strtotime('+1 day'))) {
                    $dateLabel = "Demain - " . date('d F Y', strtotime($seance['date']));
                }
        ?>
            <div class="bg-blue-50 px-4 py-2 rounded-lg">
                <h3 class="font-bold text-blue-800">
                    <i class="fas fa-calendar mr-2"></i><?= $dateLabel ?>
                </h3>
            </div>
        <?php endif; ?>
        
        <div class="card hover:shadow-lg transition">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-start gap-4">
                        <div class="bg-blue-100 p-3 rounded-lg flex-shrink-0">
                            <i class="fas fa-film text-blue-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">
                                <?= htmlspecialchars($seance['film_titre']) ?>
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <i class="fas fa-door-open w-5 mr-2"></i>
                                    <span><?= htmlspecialchars($seance['salle_nom']) ?></span>
                                    <span class="ml-2 px-2 py-0.5 bg-gray-100 rounded text-xs">
                                        <?= $seance['salle_type'] ?>
                                    </span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-clock w-5 mr-2"></i>
                                    <span><?= $seance['film_duree'] ?> minutes</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-tag w-5 mr-2"></i>
                                    <span><?= htmlspecialchars($seance['film_genre']) ?></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-users w-5 mr-2"></i>
                                    <span><?= $seance['salle_capacite'] ?> places</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex md:flex-col items-center md:items-end gap-3">
                    <div class="bg-blue-50 px-6 py-3 rounded-lg text-center">
                        <div class="text-2xl font-bold text-blue-600">
                            <?= date('H:i', strtotime($seance['heure'])) ?>
                        </div>
                        <div class="text-xs text-gray-600">
                            <?= date('d/m/Y', strtotime($seance['date'])) ?>
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <a href="index.php?controller=seance&action=modifier&id=<?= $seance['id'] ?>" 
                           class="text-blue-600 hover:text-blue-800 transition p-2"
                           title="Modifier">
                            <i class="fas fa-edit text-lg"></i>
                        </a>
                        <a href="index.php?controller=seance&action=delete&id=<?= $seance['id'] ?>" 
                           class="text-red-600 hover:text-red-800 transition p-2"
                           title="Supprimer"
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette séance ?')">
                            <i class="fas fa-trash text-lg"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="mt-6">
        <p class="text-sm text-gray-700">
            Total : <span class="font-medium"><?= count($seances) ?></span> séance(s)
        </p>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
