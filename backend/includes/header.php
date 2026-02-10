<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'My Cinema - Gestion de cinéma' ?></title>
    
    <!-- Option 1: Via CDN (décommente pour utiliser) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-gray-800 to-gray-900 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <i class="fas fa-film text-white text-2xl mr-3"></i>
                    <span class="text-white text-xl font-bold">My Cinema</span>
                </div>
                
                <div class="hidden md:flex space-x-4">
                    <a href="index.php?controller=film&action=liste" 
                       class="text-white hover:bg-red-600 px-3 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-film mr-2"></i>Films
                    </a>
                    <a href="index.php?controller=salle&action=liste" 
                       class="text-white hover:bg-red-600 px-3 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-door-open mr-2"></i>Salles
                    </a>
                    <a href="index.php?controller=seance&action=liste" 
                       class="text-white hover:bg-red-600 px-3 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-calendar-alt mr-2"></i>Séances
                    </a>
                </div>
                
                <!-- Menu mobile -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white hover:bg-red-600 p-2 rounded-md">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Menu mobile dropdown -->
        <div id="mobile-menu" class="hidden md:hidden bg-gray-800">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="index.php?controller=film&action=liste" 
                   class="text-white block px-3 py-2 rounded-md hover:bg-red-600">
                    <i class="fas fa-film mr-2"></i>Films
                </a>
                <a href="index.php?controller=salle&action=liste" 
                   class="text-white block px-3 py-2 rounded-md hover:bg-red-600">
                    <i class="fas fa-door-open mr-2"></i>Salles
                </a>
                <a href="index.php?controller=seance&action=liste" 
                   class="text-white block px-3 py-2 rounded-md hover:bg-red-600">
                    <i class="fas fa-calendar-alt mr-2"></i>Séances
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Conteneur principal -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
