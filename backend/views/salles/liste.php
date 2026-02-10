<?php $pageTitle = 'Liste des salles - My Cinema'; ?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="mb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <i class="fas fa-door-open text-green-600"></i> Gestion des Salles
            </h1>
            <p class="text-gray-600">Gérez vos salles de projection</p>
        </div>
        <a href="index.php?controller=salle&action=ajouter" 
           class="btn-primary inline-flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Ajouter une salle
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
            <p><?= htmlspecialchars($_SESSION['error']) ?></p>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Tableau des salles -->
<?php if (empty($salles)): ?>
    <div class="card text-center py-12">
        <i class="fas fa-door-open text-gray-300 text-6xl mb-4"></i>
        <p class="text-gray-500 text-lg">Aucune salle trouvée</p>
        <a href="index.php?controller=salle&action=ajouter" class="btn-primary mt-4 inline-block">
            Créer votre première salle
        </a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($salles as $salle): ?>
        <div class="card hover:shadow-xl transition">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">
                        <?= htmlspecialchars($salle['nom']) ?>
                    </h3>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                        <?php
                        switch($salle['type']) {
                            case '3D':
                                echo 'bg-blue-100 text-blue-800';
                                break;
                            case 'IMAX':
                                echo 'bg-green-100 text-green-800';
                                break;
                            case '4DX':
                                echo 'bg-orange-100 text-orange-800';
                                break;
                            default:
                                echo 'bg-gray-100 text-gray-800';
                        }
                        ?>">
                        <?= htmlspecialchars($salle['type']) ?>
                    </span>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-door-open text-green-600 text-xl"></i>
                </div>
            </div>
            
            <div class="space-y-3 mb-4">
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-users w-5 mr-3"></i>
                    <span><?= $salle['capacite'] ?> places</span>
                </div>
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-calendar-alt w-5 mr-3"></i>
                    <span><?= $salle['nb_seances'] ?? 0 ?> séance(s)</span>
                </div>
            </div>
            
            <div class="flex gap-2 pt-4 border-t border-gray-200">
                <a href="index.php?controller=salle&action=modifier&id=<?= $salle['id'] ?>" 
                   class="flex-1 text-center bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-edit mr-1"></i> Modifier
                </a>
                <a href="index.php?controller=salle&action=delete&id=<?= $salle['id'] ?>" 
                   class="flex-1 text-center bg-red-50 hover:bg-red-100 text-red-700 font-medium py-2 px-4 rounded-lg transition"
                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette salle ?')">
                    <i class="fas fa-trash mr-1"></i> Supprimer
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="mt-6">
        <p class="text-sm text-gray-700">
            Total : <span class="font-medium"><?= count($salles) ?></span> salle(s)
        </p>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
