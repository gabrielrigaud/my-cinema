<?php $pageTitle = 'Modifier une séance - My Cinema'; ?>
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
            <li class="text-gray-900 font-medium">Modifier la séance</li>
        </ol>
    </nav>
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas fa-edit text-blue-600"></i> Modifier la séance
        </h1>
        <p class="text-gray-600">
            <?= htmlspecialchars($seance['film_titre']) ?> - 
            <?= date('d/m/Y', strtotime($seance['date'])) ?> à 
            <?= date('H:i', strtotime($seance['heure'])) ?>
        </p>
    </div>
    
    <div class="card">
        <form method="POST" action="index.php?controller=seance&action=update" class="space-y-6">
            <input type="hidden" name="id" value="<?= $seance['id'] ?>">
            
            <div>
                <label for="film_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Film <span class="text-red-500">*</span>
                </label>
                <select id="film_id" name="film_id" required class="input-field">
                    <option value="">Sélectionnez un film</option>
                    <?php foreach ($films as $film): ?>
                        <option value="<?= $film['id'] ?>" 
                                data-duree="<?= $film['duree'] ?>"
                                <?= $film['id'] == $seance['film_id'] ? 'selected' : '' ?>>
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
                        <option value="<?= $salle['id'] ?>"
                                <?= $salle['id'] == $seance['salle_id'] ? 'selected' : '' ?>>
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
                               value="<?= $seance['date'] ?>"
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
                               value="<?= substr($seance['heure'], 0, 5) ?>"
                               class="input-field pl-10">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-clock text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Attention : Toute modification peut créer un conflit avec d'autres séances 
                            programmées dans la même salle.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 justify-end pt-4 border-t border-gray-200">
                <a href="index.php?controller=seance&action=liste" 
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

// Déclencher l'affichage initial
document.getElementById('film_id').dispatchEvent(new Event('change'));
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
