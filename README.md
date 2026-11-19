# My Cinema

Application web de gestion de complexe cinématographique (films, salles, séances et tableau de bord) développée en PHP orienté objet selon une architecture MVC avec couche Repository.

Démo en ligne : [solowia.fr/my-cinema/backend/index.php](https://solowia.fr/my-cinema/backend/index.php)

## Stack technique

- **Back-end :** PHP 8, PDO (`utf8mb4`, `PDO::ATTR_EMULATE_PREPARES => false`)
- **Base de données :** MySQL (`script.sql` avec clés étrangères `ON DELETE RESTRICT` et index)
- **Front-end :** Tailwind CSS, JavaScript natif (`frontend/js/app.js`)

## Architecture et choix techniques

- **Front Controller (`backend/index.php`) :** routage centralisé par paramètres `?controller=...&action=...` vers `DashboardController`, `FilmController`, `SalleController` et `SeanceController`.
- **Couche Repository (`backend/repositories/`) :** isolation des requêtes SQL préparées dans `FilmRepository`, `SalleRepository` et `SeanceRepository`.
- **Intégrité des données :**
  - Contraintes relationnelles MySQL (`ON DELETE RESTRICT`).
  - Suppression logique (`is_deleted = 1`) sur les films et salles avec vérification préalable des séances associées (`hasSeances()`).
- **Validation & Retours utilisateur :** validation côté serveur (durées, années, champs obligatoires) et messages flash en session (`$_SESSION['success']`, `$_SESSION['error']`).

## Structure

```text
my-cinema/
├── backend/
│   ├── index.php            # Front Controller
│   ├── config/database.php  # Connexion PDO
│   ├── controllers/         # Contrôleurs MVC
│   ├── repositories/        # Requêtes SQL préparées
│   ├── includes/            # Header & footer
│   └── views/               # Vues films, salles, séances, dashboard
├── frontend/
│   ├── css/input.css
│   └── js/app.js
└── script.sql               # Schéma MySQL
```

## Installation locale

1. Cloner le dépôt :
   ```bash
   git clone https://github.com/gabrielrigaud/my-cinema.git
   cd my-cinema
   ```
2. Créer la base `my_cinema` et importer `script.sql` :
   ```bash
   mysql -u root -p -e "CREATE DATABASE my_cinema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p my_cinema < script.sql
   ```
3. Configurer `backend/config/database.php` puis lancer le serveur :
   ```bash
   npm install
   npm run dev
   ```
   Ou directement avec PHP : `php -S localhost:8000 -t backend`.
