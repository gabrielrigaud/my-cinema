<?php $pageTitle = 'Programmer une séance - My Cinema'; ?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="max-w-3xl mx-auto">
    <nav class="mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm text-gray-600">
            <li>
                <a href="index.php?controller=seance&action=liste" class="hover:text-blue-600 transition">
                    <i class="fas fa-calendar-alt mr-1"></i>Séances
                </a>
            </li>
            <li><i class="fas fa-chevron-right text-gray-400"></i></li>
            <li class="text-gray-900 font-medium">Programmer une séance</li>
        </ol>
    </nav>
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas fa-plus-circle text-blue-600"></i> Programmer une nouvelle séance
        </h1>
        <p class="text-gray-600">Planifiez une projection en sélectionnant un film, une salle et un horaire</p>
    </div>
    
    <div class="card">
        <form method="POST" action="index.php?controller=seance&action=create" class="space-y-6" id="seanceForm">
            
            <div>
                <label for="film_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Film <span class="text-red-500">*</span>
                </label>
                <select id="film_id" name="film_id" required class="input-field">
                    <option value="">Sélectionnez un film</option>
                    <?php foreach ($films as $film): ?>
                        <option value="<?= $film['id'] ?>" data-duree="<?= $film['duree'] ?>">
                            <?= htmlspecialchars($film['titre']) ?> 
                            (<?= $film['duree'] ?>min - <?= $film['annee'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1 text-sm text-gray-500" id="filmDuree"></p>
            </div>
            
            <div>
                <label for="salle_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Salle <span class="text-red-500">*</span>
                </label>
                <select id="salle_id" name="salle_id" required class="input-field">
                    <option value="">Sélectionnez une salle</option>
                    <?php foreach ($salles as $salle): ?>
                        <option value="<?= $salle['id'] ?>">
                            <?= htmlspecialchars($salle['nom']) ?> 
                            (<?= $salle['type'] ?> - <?= $salle['capacite'] ?> places)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                        Date <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="date" 
                               id="date" 
                               name="date" 
                               required 
                               min="<?= date('Y-m-d') ?>"
                               value="<?= date('Y-m-d') ?>"
                               class="input-field pl-10">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar text-gray-400"></i>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="heure" class="block text-sm font-medium text-gray-700 mb-2">
                        Heure <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="time" 
                               id="heure" 
                               name="heure" 
                               required
                               class="input-field pl-10">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-clock text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 mb-1">Informations importantes</h3>
                        <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                            <li>Un délai de 15 minutes est automatiquement ajouté entre les séances</li>
                            <li>Le système vérifie automatiquement les conflits d'horaires</li>
                            <li>La date ne peut pas être dans le passé</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 justify-end pt-4 border-t border-gray-200">
                <a href="index.php?controller=seance&action=liste" 
                   class="btn-secondary text-center">
                    <i class="fas fa-times mr-2"></i>Annuler
                </a>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save mr-2"></i>Programmer la séance
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Afficher la durée du film sélectionné
document.getElementById('film_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const duree = selectedOption.getAttribute('data-duree');
    const dureeElement = document.getElementById('filmDuree');
    
    if (duree) {
        const heures = Math.floor(duree / 60);
        const minutes = duree % 60;
        dureeElement.textContent = `Durée: ${duree} minutes (${heures}h${minutes.toString().padStart(2, '0')})`;
    } else {
        dureeElement.textContent = '';
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
