<?php $pageTitle = 'Ajouter une salle - My Cinema'; ?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="max-w-3xl mx-auto">
    <nav class="mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm text-gray-600">
            <li>
                <a href="index.php?controller=salle&action=liste" class="hover:text-green-600 transition">
                    <i class="fas fa-door-open mr-1"></i>Salles
                </a>
            </li>
            <li><i class="fas fa-chevron-right text-gray-400"></i></li>
            <li class="text-gray-900 font-medium">Ajouter une salle</li>
        </ol>
    </nav>
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas fa-plus-circle text-green-600"></i> Créer une nouvelle salle
        </h1>
        <p class="text-gray-600">Remplissez le formulaire ci-dessous pour créer une salle</p>
    </div>
    
    <div class="card">
        <form method="POST" action="index.php?controller=salle&action=create" class="space-y-6">
            
            <div>
                <label for="nom" class="block text-sm font-medium text-gray-700 mb-2">
                    Nom de la salle <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="nom" 
                       name="nom" 
                       required 
                       maxlength="100"
                       class="input-field"
                       placeholder="Ex: Salle 1, Auditorium">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="capacite" class="block text-sm font-medium text-gray-700 mb-2">
                        Capacité (places) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               id="capacite" 
                               name="capacite" 
                               required 
                               min="1" 
                               max="1000"
                               class="input-field pl-10"
                               placeholder="200">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-users text-gray-400"></i>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        Type de salle <span class="text-red-500">*</span>
                    </label>
                    <select id="type" name="type" required class="input-field">
                        <option value="Standard">Standard</option>
                        <option value="3D">3D</option>
                        <option value="IMAX">IMAX</option>
                        <option value="4DX">4DX</option>
                    </select>
                </div>
            </div>
            
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 mb-1">Types de salles</h3>
                        <div class="text-sm text-blue-700 space-y-1">
                            <p><strong>Standard</strong> : Salle classique avec écran traditionnel</p>
                            <p><strong>3D</strong> : Salle équipée pour la projection 3D</p>
                            <p><strong>IMAX</strong> : Grand écran haute résolution</p>
                            <p><strong>4DX</strong> : Expérience immersive avec effets spéciaux</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 justify-end pt-4 border-t border-gray-200">
                <a href="index.php?controller=salle&action=liste" 
                   class="btn-secondary text-center">
                    <i class="fas fa-times mr-2"></i>Annuler
                </a>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save mr-2"></i>Créer la salle
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
