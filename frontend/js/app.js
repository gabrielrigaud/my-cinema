/**
 * Application principale - Gestion de l'interface utilisateur
 */

// Variables globales
let currentSection = 'films';
let currentFilmId = null;
let currentSalleId = null;
let currentSeanceId = null;

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialise l'application
 */
function initializeApp() {
    // Configuration initiale
    setMinDateTime();
    initPlanningDates();
    
    // Gestion de la navigation
    setupNavigation();
    
    // Gestion des formulaires
    setupForms();
    
    // Gestion des modaux
    setupModals();
    
    // Charger la section par défaut
    showSection('films');
    
    // Charger les données initiales
    loadInitialData();
}

/**
 * Configure la navigation
 */
function setupNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.getAttribute('data-section');
            showSection(section);
        });
    });
}

/**
 * Affiche une section spécifique
 */
function showSection(sectionName) {
    // Masquer toutes les sections
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    
    // Afficher la section demandée
    const targetSection = document.getElementById(`${sectionName}-section`);
    if (targetSection) {
        targetSection.classList.add('active');
    }
    
    // Mettre à jour la navigation
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('data-section') === sectionName) {
            link.classList.add('active');
        }
    });
    
    currentSection = sectionName;
    
    // Charger les données de la section
    loadSectionData(sectionName);
}

/**
 * Charge les données d'une section
 */
function loadSectionData(sectionName) {
    switch (sectionName) {
        case 'films':
            loadFilms();
            loadFilmFilters();
            break;
        case 'salles':
            loadSalles();
            break;
        case 'seances':
            loadSeances();
            loadSeanceFilters();
            break;
        case 'planning':
            // Le planning est chargé manuellement via le bouton
            break;
    }
}

/**
 * Charge les données initiales
 */
async function loadInitialData() {
    try {
        // Charger les salles pour les sélecteurs
        await loadSallesForSelect();
        
        // Charger les films pour les sélecteurs
        await loadFilmsForSelect();
    } catch (error) {
        console.error('Error loading initial data:', error);
    }
}

/**
 * Configure les formulaires
 */
function setupForms() {
    // Formulaire des films
    const filmForm = document.getElementById('film-form');
    if (filmForm) {
        filmForm.addEventListener('submit', handleFilmSubmit);
    }
    
    // Formulaire des salles
    const salleForm = document.getElementById('salle-form');
    if (salleForm) {
        salleForm.addEventListener('submit', handleSalleSubmit);
    }
    
    // Formulaire des séances
    const seanceForm = document.getElementById('seance-form');
    if (seanceForm) {
        seanceForm.addEventListener('submit', handleSeanceSubmit);
    }
}

/**
 * Configure les modaux
 */
function setupModals() {
    // Fermer les modaux en cliquant à l'extérieur
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
            }
        });
    });
    
    // Gérer la touche Échap
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
}

/**
 * Ferme tous les modaux
 */
function closeAllModals() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.classList.remove('show');
    });
}

/**
 * Configure les filtres
 */
function setupFilters() {
    // Filtres des films
    const filmSearch = document.getElementById('film-search');
    const filmGenre = document.getElementById('film-genre');
    const filmAnnee = document.getElementById('film-annee');
    
    if (filmSearch) filmSearch.addEventListener('input', () => loadFilms());
    if (filmGenre) filmGenre.addEventListener('change', () => loadFilms());
    if (filmAnnee) filmAnnee.addEventListener('change', () => loadFilms());
    
    // Filtres des salles
    const salleSearch = document.getElementById('salle-search');
    if (salleSearch) salleSearch.addEventListener('input', () => loadSalles());
    
    // Filtres des séances
    const seanceSearch = document.getElementById('seance-search');
    const seanceFilm = document.getElementById('seance-film');
    const seanceSalle = document.getElementById('seance-salle');
    
    if (seanceSearch) seanceSearch.addEventListener('input', () => loadSeances());
    if (seanceFilm) seanceFilm.addEventListener('change', () => loadSeances());
    if (seanceSalle) seanceSalle.addEventListener('change', () => loadSeances());
}

/**
 * Gestionnaires d'événements pour les formulaires
 */

// Gestionnaire pour le formulaire des films
async function handleFilmSubmit(e) {
    e.preventDefault();
    
    if (!validateForm('film-form')) {
        showToast('Veuillez remplir tous les champs obligatoires', 'warning');
        return;
    }
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    try {
        if (currentFilmId) {
            await api.updateFilm(currentFilmId, data);
            showToast('Film mis à jour avec succès', 'success');
        } else {
            await api.createFilm(data);
            showToast('Film créé avec succès', 'success');
        }
        
        closeFilmModal();
        loadFilms();
        loadFilmFilters();
    } catch (error) {
        handleApiError(error);
    }
}

// Gestionnaire pour le formulaire des salles
async function handleSalleSubmit(e) {
    e.preventDefault();
    
    if (!validateForm('salle-form')) {
        showToast('Veuillez remplir tous les champs obligatoires', 'warning');
        return;
    }
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    data.capacite = parseInt(data.capacite);
    
    try {
        if (currentSalleId) {
            await api.updateSalle(currentSalleId, data);
            showToast('Salle mise à jour avec succès', 'success');
        } else {
            await api.createSalle(data);
            showToast('Salle créée avec succès', 'success');
        }
        
        closeSalleModal();
        loadSalles();
        loadSallesForSelect();
    } catch (error) {
        handleApiError(error);
    }
}

// Gestionnaire pour le formulaire des séances
async function handleSeanceSubmit(e) {
    e.preventDefault();
    
    if (!validateForm('seance-form')) {
        showToast('Veuillez remplir tous les champs obligatoires', 'warning');
        return;
    }
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    data.film_id = parseInt(data.film_id);
    data.salle_id = parseInt(data.salle_id);
    data.prix = parseFloat(data.prix);
    
    try {
        if (currentSeanceId) {
            await api.updateSeance(currentSeanceId, data);
            showToast('Séance mise à jour avec succès', 'success');
        } else {
            await api.createSeance(data);
            showToast('Séance créée avec succès', 'success');
        }
        
        closeSeanceModal();
        loadSeances();
    } catch (error) {
        handleApiError(error);
    }
}

/**
 * Fonctions utilitaires pour les modaux
 */

function openFilmModal(filmId = null) {
    currentFilmId = filmId;
    const modal = document.getElementById('film-modal');
    const title = document.getElementById('film-modal-title');
    const form = document.getElementById('film-form');
    
    if (filmId) {
        title.textContent = 'Modifier le film';
        loadFilmForEdit(filmId);
    } else {
        title.textContent = 'Ajouter un film';
        resetForm('film-form');
    }
    
    modal.classList.add('show');
}

function closeFilmModal() {
    document.getElementById('film-modal').classList.remove('show');
    currentFilmId = null;
    resetForm('film-form');
}

function openSalleModal(salleId = null) {
    currentSalleId = salleId;
    const modal = document.getElementById('salle-modal');
    const title = document.getElementById('salle-modal-title');
    const form = document.getElementById('salle-form');
    
    if (salleId) {
        title.textContent = 'Modifier la salle';
        loadSalleForEdit(salleId);
    } else {
        title.textContent = 'Ajouter une salle';
        resetForm('salle-form');
    }
    
    modal.classList.add('show');
}

function closeSalleModal() {
    document.getElementById('salle-modal').classList.remove('show');
    currentSalleId = null;
    resetForm('salle-form');
}

function openSeanceModal(seanceId = null) {
    currentSeanceId = seanceId;
    const modal = document.getElementById('seance-modal');
    const title = document.getElementById('seance-modal-title');
    const form = document.getElementById('seance-form');
    
    if (seanceId) {
        title.textContent = 'Modifier la séance';
        loadSeanceForEdit(seanceId);
    } else {
        title.textContent = 'Ajouter une séance';
        resetForm('seance-form');
    }
    
    modal.classList.add('show');
}

function closeSeanceModal() {
    document.getElementById('seance-modal').classList.remove('show');
    currentSeanceId = null;
    resetForm('seance-form');
}

function openConfirmModal(message, onConfirm) {
    const modal = document.getElementById('confirm-modal');
    const messageElement = document.getElementById('confirm-message');
    const confirmBtn = document.getElementById('confirm-btn');
    
    messageElement.textContent = message;
    
    // Supprimer les anciens écouteurs
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    // Ajouter le nouvel écouteur
    newConfirmBtn.addEventListener('click', function() {
        onConfirm();
        closeConfirmModal();
    });
    
    modal.classList.add('show');
}

function closeConfirmModal() {
    document.getElementById('confirm-modal').classList.remove('show');
}

/**
 * Fonctions de réinitialisation des filtres
 */
function resetFilmFilters() {
    document.getElementById('film-search').value = '';
    document.getElementById('film-genre').value = '';
    document.getElementById('film-annee').value = '';
    loadFilms();
}

function resetSalleFilters() {
    document.getElementById('salle-search').value = '';
    loadSalles();
}

function resetSeanceFilters() {
    document.getElementById('seance-search').value = '';
    document.getElementById('seance-film').value = '';
    document.getElementById('seance-salle').value = '';
    loadSeances();
}

/**
 * Fonctions de chargement pour l'édition
 */
async function loadFilmForEdit(filmId) {
    try {
        const response = await api.getFilm(filmId);
        const film = response.data;
        
        document.getElementById('film-titre').value = film.titre;
        document.getElementById('film-description').value = film.description || '';
        document.getElementById('film-duree').value = film.duree;
        document.getElementById('film-annee-sortie').value = film.annee_sortie;
        document.getElementById('film-genre').value = film.genre;
        document.getElementById('film-affiche').value = film.affiche || '';
    } catch (error) {
        handleApiError(error);
    }
}

async function loadSalleForEdit(salleId) {
    try {
        const response = await api.getSalle(salleId);
        const salle = response.data;
        
        document.getElementById('salle-nom').value = salle.nom;
        document.getElementById('salle-capacite').value = salle.capacite;
        document.getElementById('salle-type').value = salle.type;
    } catch (error) {
        handleApiError(error);
    }
}

async function loadSeanceForEdit(seanceId) {
    try {
        const response = await api.getSeance(seanceId);
        const seance = response.data;
        
        document.getElementById('seance-film-id').value = seance.film_id;
        document.getElementById('seance-salle-id').value = seance.salle_id;
        
        // Formater la date pour le champ datetime-local
        const date = new Date(seance.date_seance);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        
        document.getElementById('seance-date-seance').value = `${year}-${month}-${day}T${hours}:${minutes}`;
        document.getElementById('seance-prix').value = seance.prix;
    } catch (error) {
        handleApiError(error);
    }
}
