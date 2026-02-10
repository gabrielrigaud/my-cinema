<!-- Contenu liste des films -->

<div class="mb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <i class="fas fa-film text-red-600"></i> Gestion des Films
            </h1>
            <p class="text-gray-600">Gérez votre catalogue de films</p>
        </div>
        <a href="index.php?controller=film&action=ajouter" 
           class="btn-primary inline-flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Ajouter un film
        </a>
    </div>
</div>

<!-- Messages de succès/erreur -->
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

<!-- Filtres -->
<div class="card mb-6">
    <form method="GET" action="index.php" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="hidden" name="controller" value="film">
        <input type="hidden" name="action" value="liste">
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
            <input type="text" name="search" 
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                   placeholder="Titre du film..." 
                   class="input-field">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Genre</label>
            <select name="genre" class="input-field">
                <option value="">Tous les genres</option>
                <option value="Action" <?= ($_GET['genre'] ?? '') === 'Action' ? 'selected' : '' ?>>Action</option>
                <option value="Comédie" <?= ($_GET['genre'] ?? '') === 'Comédie' ? 'selected' : '' ?>>Comédie</option>
                <option value="Drame" <?= ($_GET['genre'] ?? '') === 'Drame' ? 'selected' : '' ?>>Drame</option>
                <option value="Science-Fiction" <?= ($_GET['genre'] ?? '') === 'Science-Fiction' ? 'selected' : '' ?>>Science-Fiction</option>
                <option value="Horreur" <?= ($_GET['genre'] ?? '') === 'Horreur' ? 'selected' : '' ?>>Horreur</option>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Année</label>
            <input type="number" name="annee" 
                   value="<?= htmlspecialchars($_GET['annee'] ?? '') ?>"
                   placeholder="2024" 
                   class="input-field">
        </div>
        
        <div class="flex items-end">
            <button type="submit" class="btn-primary w-full">
                <i class="fas fa-search mr-2"></i>Filtrer
            </button>
        </div>
    </form>
</div>

<!-- Tableau des films -->
<?php if (empty($films)): ?>
    <div class="card text-center py-12">
        <i class="fas fa-film text-gray-300 text-6xl mb-4"></i>
        <p class="text-gray-500 text-lg">Aucun film trouvé</p>
        <a href="index.php?controller=film&action=ajouter" class="btn-primary mt-4 inline-block">
            Ajouter votre premier film
        </a>
    </div>
<?php else: ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-header">
                            <i class="fas fa-film mr-2"></i>Titre
                        </th>
                        <th class="table-header">
                            <i class="fas fa-clock mr-2"></i>Durée
                        </th>
                        <th class="table-header">
                            <i class="fas fa-calendar mr-2"></i>Année
                        </th>
                        <th class="table-header">
                            <i class="fas fa-tag mr-2"></i>Genre
                        </th>
                        <th class="table-header text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($films as $film): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="table-cell">
                            <span class="font-medium text-gray-900">
                                <?= htmlspecialchars($film['titre']) ?>
                            </span>
                        </td>
                        <td class="table-cell">
                            <span class="text-gray-600"><?= $film['duree'] ?> min</span>
                        </td>
                        <td class="table-cell">
                            <span class="text-gray-600"><?= $film['annee'] ?></span>
                        </td>
                        <td class="table-cell">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <?= htmlspecialchars($film['genre']) ?>
                            </span>
                        </td>
                        <td class="table-cell">
                            <div class="flex justify-center space-x-2">
                                <a href="index.php?controller=film&action=modifier&id=<?= $film['id'] ?>" 
                                   class="text-blue-600 hover:text-blue-800 transition"
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="index.php?controller=film&action=delete&id=<?= $film['id'] ?>" 
                                   class="text-red-600 hover:text-red-800 transition"
                                   title="Supprimer"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce film ?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination (à implémenter) -->
    <div class="mt-6 flex justify-between items-center">
        <p class="text-sm text-gray-700">
            Affichage de <span class="font-medium"><?= count($films) ?></span> film(s)
        </p>
        <!-- Pagination buttons here -->
    </div>
<?php endif; ?>

<!-- Fin contenu -->
