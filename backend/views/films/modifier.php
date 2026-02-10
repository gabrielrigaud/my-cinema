<!-- Contenu modifier film -->

<div class="max-w-3xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm text-gray-600">
            <li>
                <a href="index.php?controller=film&action=liste" class="hover:text-red-600 transition">
                    <i class="fas fa-film mr-1"></i>Films
                </a>
            </li>
            <li><i class="fas fa-chevron-right text-gray-400"></i></li>
            <li class="text-gray-900 font-medium">Modifier : <?= htmlspecialchars($film['titre']) ?></li>
        </ol>
    </nav>
    
    <!-- Titre de la page -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas fa-edit text-red-600"></i> Modifier le film
        </h1>
        <p class="text-gray-600">Modifiez les informations du film ci-dessous</p>
    </div>
    
    <!-- Formulaire -->
    <div class="card">
        <form method="POST" action="index.php?controller=film&action=update" class="space-y-6">
            <input type="hidden" name="id" value="<?= $film['id'] ?>">
            
            <!-- Titre -->
            <div>
                <label for="titre" class="block text-sm font-medium text-gray-700 mb-2">
                    Titre du film <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="titre" 
                       name="titre" 
                       required 
                       maxlength="255"
                       value="<?= htmlspecialchars($film['titre']) ?>"
                       class="input-field"
                       placeholder="Ex: Inception">
            </div>
            
            <!-- Durée et Année -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Durée -->
                <div>
                    <label for="duree" class="block text-sm font-medium text-gray-700 mb-2">
                        Durée (minutes) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               id="duree" 
                               name="duree" 
                               required 
                               min="1" 
                               max="500"
                               value="<?= $film['duree'] ?>"
                               class="input-field pl-10"
                               placeholder="148">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-clock text-gray-400"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Année -->
                <div>
                    <label for="annee" class="block text-sm font-medium text-gray-700 mb-2">
                        Année de sortie <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               id="annee" 
                               name="annee" 
                               required 
                               min="1888" 
                               max="<?= date('Y') + 2 ?>"
                               value="<?= $film['annee'] ?>"
                               class="input-field pl-10"
                               placeholder="2024">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Genre -->
            <div>
                <label for="genre" class="block text-sm font-medium text-gray-700 mb-2">
                    Genre <span class="text-red-500">*</span>
                </label>
                <select id="genre" name="genre" required class="input-field">
                    <option value="">Sélectionnez un genre</option>
                    <?php 
                    $genres = ['Action', 'Animation', 'Aventure', 'Comédie', 'Documentaire', 'Drame', 
                               'Fantastique', 'Guerre', 'Historique', 'Horreur', 'Musical', 'Policier', 
                               'Romance', 'Science-Fiction', 'Thriller', 'Western'];
                    foreach ($genres as $g): 
                    ?>
                        <option value="<?= $g ?>" <?= $film['genre'] === $g ? 'selected' : '' ?>>
                            <?= $g ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Informations supplémentaires -->
            <?php if (isset($film['nb_seances']) && $film['nb_seances'] > 0): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Ce film est associé à <strong><?= $film['nb_seances'] ?> séance(s)</strong>. 
                            Toute modification impactera les séances existantes.
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Boutons d'action -->
            <div class="flex flex-col sm:flex-row gap-3 justify-end pt-4 border-t border-gray-200">
                <a href="index.php?controller=film&action=liste" 
                   class="btn-secondary text-center">
                    <i class="fas fa-times mr-2"></i>Annuler
                </a>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save mr-2"></i>Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Fin contenu -->
