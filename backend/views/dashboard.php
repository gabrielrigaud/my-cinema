<!-- Contenu du dashboard -->

<!-- En-tête du dashboard -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">
        <i class="fas fa-tachometer-alt text-cinema-primary"></i> Tableau de bord
    </h1>
    <p class="text-gray-600">Vue d'ensemble de votre cinéma</p>
</div>

<!-- Statistiques rapides -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Films -->
    <div class="card hover:shadow-lg transition cursor-pointer" 
         onclick="window.location.href='index.php?controller=film&action=liste'">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Films au catalogue</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['total_films'] ?? 0 ?></p>
            </div>
            <div class="bg-red-100 p-4 rounded-full">
                <i class="fas fa-film text-cinema-primary text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-green-600 font-medium">
                <i class="fas fa-arrow-up mr-1"></i>
                <?= $stats['nouveaux_films'] ?? 0 ?> ce mois
            </span>
        </div>
    </div>
    
    <!-- Salles -->
    <div class="card hover:shadow-lg transition cursor-pointer"
         onclick="window.location.href='index.php?controller=salle&action=liste'">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Salles disponibles</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['total_salles'] ?? 0 ?></p>
            </div>
            <div class="bg-blue-100 p-4 rounded-full">
                <i class="fas fa-door-open text-blue-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-gray-600">
                <i class="fas fa-users mr-1"></i>
                <?= $stats['capacite_totale'] ?? 0 ?> places au total
            </span>
        </div>
    </div>
    
    <!-- Séances aujourd'hui -->
    <div class="card hover:shadow-lg transition cursor-pointer"
         onclick="window.location.href='index.php?controller=seance&action=liste'">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Séances aujourd'hui</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['seances_aujourdhui'] ?? 0 ?></p>
            </div>
            <div class="bg-green-100 p-4 rounded-full">
                <i class="fas fa-calendar-check text-green-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-gray-600">
                <i class="fas fa-calendar-week mr-1"></i>
                <?= $stats['seances_semaine'] ?? 0 ?> cette semaine
            </span>
        </div>
    </div>
    
    <!-- Séances à venir -->
    <div class="card hover:shadow-lg transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Séances à venir</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['seances_a_venir'] ?? 0 ?></p>
            </div>
            <div class="bg-orange-100 p-4 rounded-full">
                <i class="fas fa-clock text-orange-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-gray-600">
                <i class="fas fa-calendar-alt mr-1"></i>
                Prochains 7 jours
            </span>
        </div>
    </div>
</div>

<!-- Actions rapides -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <a href="index.php?controller=film&action=ajouter" 
       class="card hover:bg-red-50 hover:border-red-300 border-2 border-transparent transition group">
        <div class="flex items-center">
            <div class="bg-red-100 group-hover:bg-red-200 p-4 rounded-lg mr-4 transition">
                <i class="fas fa-plus text-cinema-primary text-xl"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 mb-1">Ajouter un film</h3>
                <p class="text-sm text-gray-600">Enrichissez votre catalogue</p>
            </div>
        </div>
    </a>
    
    <a href="index.php?controller=salle&action=ajouter" 
       class="card hover:bg-blue-50 hover:border-blue-300 border-2 border-transparent transition group">
        <div class="flex items-center">
            <div class="bg-blue-100 group-hover:bg-blue-200 p-4 rounded-lg mr-4 transition">
                <i class="fas fa-plus text-blue-600 text-xl"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 mb-1">Créer une salle</h3>
                <p class="text-sm text-gray-600">Ajoutez une nouvelle salle</p>
            </div>
        </div>
    </a>
    
    <a href="index.php?controller=seance&action=ajouter" 
       class="card hover:bg-green-50 hover:border-green-300 border-2 border-transparent transition group">
        <div class="flex items-center">
            <div class="bg-green-100 group-hover:bg-green-200 p-4 rounded-lg mr-4 transition">
                <i class="fas fa-plus text-green-600 text-xl"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 mb-1">Programmer une séance</h3>
                <p class="text-sm text-gray-600">Planifiez une projection</p>
            </div>
        </div>
    </a>
</div>

<!-- Prochaines séances -->
<div class="card">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-800">
            <i class="fas fa-calendar-day text-cinema-primary mr-2"></i>
            Prochaines séances
        </h2>
        <a href="index.php?controller=seance&action=liste" 
           class="text-cinema-primary hover:text-cinema-primary text-sm font-medium transition">
            Voir tout <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
    
    <?php if (!empty($prochaines_seances)): ?>
        <div class="space-y-4">
            <?php foreach (array_slice($prochaines_seances, 0, 5) as $seance): ?>
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                <div class="flex items-center space-x-4">
                    <div class="bg-red-100 p-3 rounded-lg">
                        <i class="fas fa-film text-cinema-primary"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">
                            <?= htmlspecialchars($seance['film_titre']) ?>
                        </h3>
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-door-open mr-1"></i>
                            <?= htmlspecialchars($seance['salle_nom']) ?>
                            <span class="mx-2">•</span>
                            <i class="fas fa-clock mr-1"></i>
                            <?= $seance['duree'] ?> min
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-medium text-gray-900">
                        <?= date('H:i', strtotime($seance['heure'])) ?>
                    </p>
                    <p class="text-sm text-gray-600">
                        <?= date('d/m/Y', strtotime($seance['date'])) ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-calendar-times text-gray-300 text-5xl mb-4"></i>
            <p class="text-gray-500">Aucune séance programmée</p>
            <a href="index.php?controller=seance&action=ajouter" 
               class="btn-primary mt-4 inline-block">
                Programmer une séance
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Fin du contenu -->
