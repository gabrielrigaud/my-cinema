# My Cinema - Application de Gestion de Cinéma

Application web de gestion de cinéma développée en PHP/MySQL avec architecture MVC et interface Tailwind CSS.

## 🛠️ Technologies Utilisées

- **Backend** : PHP 8.3+
- **Base de données** : MySQL 8.0+
- **Frontend** : HTML5, Tailwind CSS, JavaScript
- **Architecture** : MVC (Model-View-Controller)

## 📦 Installation

### 1. Prérequis

- PHP 8.3 ou supérieur
- MySQL 8.0 ou supérieur
- Serveur web (Apache/Nginx) ou PHP built-in server
- Node.js et npm (pour compiler Tailwind CSS)

### 2. Cloner le projet

```bash
git clone https://github.com/votre-username/my-cinema.git
cd my-cinema
```

### 3. Configuration de la base de données

```bash
# Se connecter à MySQL
mysql -u root -p

# Créer la base de données
CREATE DATABASE my_cinema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Importer le schéma
mysql -u root -p my_cinema < script.sql
```

### 4. Installation de Tailwind CSS

#### Option A : Via CDN (Développement rapide)

Décommentez cette ligne dans `backend/includes/header.php` :

```html
<script src="https://cdn.tailwindcss.com"></script>
```

#### Option B : Installation complète (Recommandé)

```bash
# Installer les dépendances
npm install

# Compiler Tailwind CSS
npm run build:css

# Ou en mode watch (recompile automatiquement)
npm run watch:css
```

Le fichier CSS compilé sera généré dans `frontend/css/output.css`.

### 5. Lancer l'application

```bash
# Avec le serveur PHP intégré
cd backend
php -S localhost:8000

# Ou avec XAMPP/WAMP, placez le projet dans htdocs/www
```

Accédez à l'application : `http://localhost:8000` ou `http://localhost/my-cinema/backend/`

## 📁 Structure du Projet

```
my-cinema/
├── backend/
│   ├── config/
│   │   └── database.php          # Configuration PDO
│   ├── repositories/             # Couche d'accès aux données
│   │   ├── FilmRepository.php
│   │   ├── SalleRepository.php
│   │   └── SeanceRepository.php
│   ├── controllers/              # Contrôleurs MVC
│   │   ├── FilmController.php
│   │   ├── SalleController.php
│   │   └── SeanceController.php
│   ├── views/                    # Vues (templates PHP)
│   │   ├── films/
│   │   ├── salles/
│   │   └── seances/
│   ├── includes/                 # Header et Footer
│   │   ├── header.php
│   │   └── footer.php
│   └── index.php                 # Point d'entrée (routeur)
├── frontend/
│   ├── css/
│   │   ├── input.css            # Source Tailwind
│   │   └── output.css           # CSS compilé
│   ├── js/
│   └── assets/
├── tailwind.config.js           # Configuration Tailwind
├── package.json
├── script.sql                    # Schéma de la base
└── README.md
```
