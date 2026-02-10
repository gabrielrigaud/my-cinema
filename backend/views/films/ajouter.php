<!-- Contenu ajouter film -->

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
            <li class="text-gray-900 font-medium">Ajouter un film</li>
        </ol>
    </nav>
    
    <!-- Titre de la page -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas fa-plus-circle text-red-600"></i> Ajouter un nouveau film
        </h1>
        <p class="text-gray-600">Remplissez le formulaire ci-dessous pour ajouter un film au catalogue</p>
    </div>
    
    <!-- Formulaire -->
    <div class="card">
        <form method="POST" action="index.php?controller=film&action=create" class="space-y-6">
            
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
                       class="input-field"
                       placeholder="Ex: Inception">
                <p class="mt-1 text-sm text-gray-500">Le titre doit être unique et complet</p>
            </div>
            
            <!-- Durée et Année (côte à côte) -->
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
                               value="<?= date('Y') ?>"
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
                    <option value="Action">Action</option>
                    <option value="Animation">Animation</option>
                    <option value="Aventure">Aventure</option>
                    <option value="Comédie">Comédie</option>
                    <option value="Documentaire">Documentaire</option>
                    <option value="Drame">Drame</option>
                    <option value="Fantastique">Fantastique</option>
                    <option value="Guerre">Guerre</option>
                    <option value="Historique">Historique</option>
                    <option value="Horreur">Horreur</option>
                    <option value="Musical">Musical</option>
                    <option value="Policier">Policier</option>
                    <option value="Romance">Romance</option>
                    <option value="Science-Fiction">Science-Fiction</option>
                    <option value="Thriller">Thriller</option>
                    <option value="Western">Western</option>
                </select>
            </div>
            
            <!-- Description (optionnel) -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Synopsis (optionnel)
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="4" 
                          maxlength="1000"
                          class="input-field resize-none"
                          placeholder="Entrez un court résumé du film..."></textarea>
                <p class="mt-1 text-sm text-gray-500">Maximum 1000 caractères</p>
            </div>
            
            <!-- Séparateur -->
            <div class="border-t border-gray-200 pt-6">
                <p class="text-sm text-gray-600 mb-4">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    Les champs marqués d'un <span class="text-red-500">*</span> sont obligatoires
                </p>
            </div>
            
            <!-- Boutons d'action -->
            <div class="flex flex-col sm:flex-row gap-3 justify-end">
                <a href="index.php?controller=film&action=liste" 
                   class="btn-secondary text-center">
                    <i class="fas fa-times mr-2"></i>Annuler
                </a>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save mr-2"></i>Enregistrer le film
                </button>
            </div>
        </form>
    </div>
    
    <!-- Carte d'aide -->
    <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-lightbulb text-blue-500 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 mb-1">Astuce</h3>
                <p class="text-sm text-blue-700">
                    Assurez-vous que toutes les informations sont correctes avant d'enregistrer. 
                    Vous pourrez modifier le film ultérieurement si nécessaire.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Fin contenu -->
