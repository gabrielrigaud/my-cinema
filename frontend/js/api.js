/**
 * API Service - Gestion des appels à l'API backend
 */

class ApiService {
    constructor() {
        this.baseUrl = '/backend/api';
    }

    /**
     * Effectue une requête HTTP
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        try {
            showLoading();
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        } finally {
            hideLoading();
        }
    }

    /**
     * Requête GET
     */
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return this.request(url);
    }

    /**
     * Requête POST
     */
    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    /**
     * Requête PUT
     */
    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    /**
     * Requête DELETE
     */
    async delete(endpoint) {
        return this.request(endpoint, {
            method: 'DELETE'
        });
    }

    // === MOVIES ===

    /**
     * Récupère tous les films
     */
    async getMovies(params = {}) {
        return this.get('/movies', params);
    }

    /**
     * Récupère un film par son ID
     */
    async getMovie(id) {
        return this.get(`/movies/${id}`);
    }

    /**
     * Crée un nouveau film
     */
    async createMovie(data) {
        return this.post('/movies', data);
    }

    /**
     * Met à jour un film
     */
    async updateMovie(id, data) {
        return this.put(`/movies/${id}`, data);
    }

    /**
     * Supprime un film
     */
    async deleteMovie(id) {
        return this.delete(`/movies/${id}`);
    }

    /**
     * Récupère les genres de films
     */
    async getMovieGenres() {
        return this.get('/movies/genres');
    }

    /**
     * Récupère les années de films
     */
    async getMovieYears() {
        return this.get('/movies/years');
    }

    /**
     * Récupère les réalisateurs de films
     */
    async getMovieDirectors() {
        return this.get('/movies/directors');
    }

    // === ROOMS ===

    /**
     * Récupère toutes les salles
     */
    async getRooms(params = {}) {
        return this.get('/rooms', params);
    }

    /**
     * Récupère une salle par son ID
     */
    async getRoom(id) {
        return this.get(`/rooms/${id}`);
    }

    /**
     * Crée une nouvelle salle
     */
    async createRoom(data) {
        return this.post('/rooms', data);
    }

    /**
     * Met à jour une salle
     */
    async updateRoom(id, data) {
        return this.put(`/rooms/${id}`, data);
    }

    /**
     * Supprime une salle
     */
    async deleteRoom(id) {
        return this.delete(`/rooms/${id}`);
    }

    /**
     * Récupère les salles pour les listes déroulantes
     */
    async getRoomsForSelect() {
        return this.get('/rooms/select');
    }

    /**
     * Récupère les types de salles
     */
    async getRoomTypes() {
        return this.get('/rooms/types');
    }

    /**
     * Vérifie la disponibilité d'une salle
     */
    async checkRoomAvailability(data) {
        return this.post('/rooms/check-availability', data);
    }

    /**
     * Active ou désactive une salle
     */
    async toggleRoomActive(id, active) {
        return this.patch(`/rooms/${id}`, { active });
    }

    // === SCREENINGS ===

    /**
     * Récupère toutes les séances
     */
    async getScreenings(params = {}) {
        return this.get('/screenings', params);
    }

    /**
     * Récupère une séance par son ID
     */
    async getScreening(id) {
        return this.get(`/screenings/${id}`);
    }

    /**
     * Crée une nouvelle séance
     */
    async createScreening(data) {
        return this.post('/screenings', data);
    }

    /**
     * Met à jour une séance
     */
    async updateScreening(id, data) {
        return this.put(`/screenings/${id}`, data);
    }

    /**
     * Supprime une séance
     */
    async deleteScreening(id) {
        return this.delete(`/screenings/${id}`);
    }

    /**
     * Récupère les séances pour une date donnée
     */
    async getScreeningsByDate(date, roomId = null) {
        const params = roomId ? { room_id: roomId } : {};
        return this.get(`/screenings/date/${encodeURIComponent(date)}`, params);
    }

    /**
     * Récupère le planning des séances
     */
    async getPlanning(params = {}) {
        return this.get('/screenings/planning', params);
    }

    /**
     * Récupère les séances à venir
     */
    async getUpcomingScreenings(limit = 10) {
        return this.get('/screenings/upcoming', { limit });
    }

    /**
     * Récupère les séances passées récentes
     */
    async getRecentPastScreenings(limit = 10) {
        return this.get('/screenings/recent-past', { limit });
    }
}

// Instance globale de l'API
const api = new ApiService();

// === UTILITAIRES ===

/**
 * Affiche le loader
 */
function showLoading() {
    document.getElementById('loading').classList.add('show');
}

/**
 * Cache le loader
 */
function hideLoading() {
    document.getElementById('loading').classList.remove('show');
}

/**
 * Affiche une notification toast
 */
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = toast.querySelector('.toast-message');
    const toastIcon = toast.querySelector('.toast-icon');
    
    // Configurer le message
    toastMessage.textContent = message;
    
    // Configurer l'icône et la classe
    toast.className = 'toast';
    toast.classList.add(type);
    
    switch (type) {
        case 'success':
            toastIcon.className = 'toast-icon fas fa-check-circle';
            break;
        case 'error':
            toastIcon.className = 'toast-icon fas fa-exclamation-circle';
            break;
        case 'warning':
            toastIcon.className = 'toast-icon fas fa-exclamation-triangle';
            break;
        default:
            toastIcon.className = 'toast-icon fas fa-info-circle';
    }
    
    // Afficher le toast
    toast.classList.add('show');
    
    // Cacher automatiquement après 3 secondes
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

/**
 * Formate une date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

/**
 * Formate une heure
 */
function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Formate une date et heure
 */
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Formate un prix
 */
function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
}

/**
 * Valide un formulaire
 */
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });
    
    return isValid;
}

/**
 * Réinitialise un formulaire
 */
function resetForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
        const inputs = form.querySelectorAll('.error');
        inputs.forEach(input => input.classList.remove('error'));
    }
}

/**
 * Gère les erreurs d'API
 */
function handleApiError(error) {
    console.error('API Error:', error);
    
    if (error.message) {
        showToast(error.message, 'error');
    } else {
        showToast('Une erreur est survenue. Veuillez réessayer.', 'error');
    }
}

/**
 * Crée un élément HTML
 */
function createElement(tag, className = '', content = '') {
    const element = document.createElement(tag);
    if (className) element.className = className;
    if (content) element.innerHTML = content;
    return element;
}

/**
 * Vide un élément HTML
 */
function emptyElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = '';
    }
}

/**
 * Configure la date minimale pour les champs datetime-local (maintenant)
 */
function setMinDateTime() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    
    const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
    
    const datetimeInputs = document.querySelectorAll('input[type="datetime-local"]');
    datetimeInputs.forEach(input => {
        input.min = minDateTime;
    });
}

/**
 * Initialise les dates par défaut pour le planning
 */
function initPlanningDates() {
    const today = new Date();
    const nextWeek = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000);
    
    const dateDebutInput = document.getElementById('planning-date-debut');
    const dateFinInput = document.getElementById('planning-date-fin');
    
    if (dateDebutInput) {
        dateDebutInput.value = today.toISOString().split('T')[0];
    }
    
    if (dateFinInput) {
        dateFinInput.value = nextWeek.toISOString().split('T')[0];
    }
}
