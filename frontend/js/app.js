let currentSection = 'films';
let currentFilmId = null;
let currentSalleId = null;
let currentSeanceId = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    setMinDateTime();
    initPlanningDates();
    setupNavigation();
    setupForms();
    setupModals();
    setupFilters();
    showSection('films');
    loadInitialData();
}

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

function showSection(sectionName) {
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => {
        section.classList.remove('active');
    });

    const targetSection = document.getElementById(`${sectionName}-section`);
    if (targetSection) {
        targetSection.classList.add('active');
    }

    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('data-section') === sectionName) {
            link.classList.add('active');
        }
    });

    currentSection = sectionName;
    loadSectionData(sectionName);
}

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
            break;
    }
}

async function loadInitialData() {
    try {
        await loadSallesForSelect();
        await loadFilmsForSelect();
    } catch (error) {
        console.error('Error loading initial data:', error);
    }
}

function setupForms() {
    const filmForm = document.getElementById('film-form');
    if (filmForm) {
        filmForm.addEventListener('submit', handleFilmSubmit);
    }

    const salleForm = document.getElementById('salle-form');
    if (salleForm) {
        salleForm.addEventListener('submit', handleSalleSubmit);
    }

    const seanceForm = document.getElementById('seance-form');
    if (seanceForm) {
        seanceForm.addEventListener('submit', handleSeanceSubmit);
    }
}

function setupModals() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
            }
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
}

function closeAllModals() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.classList.remove('show');
    });
}

function setupFilters() {
    const filmSearch = document.getElementById('film-search');
    const filmGenre = document.getElementById('film-genre');
    const filmAnnee = document.getElementById('film-annee');

    if (filmSearch) filmSearch.addEventListener('input', () => loadFilms());
    if (filmGenre) filmGenre.addEventListener('change', () => loadFilms());
    if (filmAnnee) filmAnnee.addEventListener('change', () => loadFilms());

    const salleSearch = document.getElementById('salle-search');
    if (salleSearch) salleSearch.addEventListener('input', () => loadSalles());

    const seanceSearch = document.getElementById('seance-search');
    const seanceFilm = document.getElementById('seance-film');
    const seanceSalle = document.getElementById('seance-salle');

    if (seanceSearch) seanceSearch.addEventListener('input', () => loadSeances());
    if (seanceFilm) seanceFilm.addEventListener('change', () => loadSeances());
    if (seanceSalle) seanceSalle.addEventListener('change', () => loadSeances());
}

async function handleFilmSubmit(e) {
    e.preventDefault();

    if (!validateForm('film-form')) {
        showToast('Veuillez remplir tous les champs obligatoires', 'warning');
        return;
    }

    const formData = new FormData(e.target);

    // Mapper les champs frontend vers backend
    const data = {
        title: formData.get('titre'),
        description: formData.get('description'),
        duration: parseInt(formData.get('duree')),
        release_year: parseInt(formData.get('annee_sortie')),
        genre: formData.get('genre'),
        director: formData.get('realisateur')
    };

    try {
        if (currentFilmId) {
            await api.updateMovie(currentFilmId, data);
            showToast('Film mis à jour avec succès', 'success');
        } else {
            await api.createMovie(data);
            showToast('Film créé avec succès', 'success');
        }

        closeFilmModal();
        loadFilms();
        loadFilmFilters();
        loadFilmsForSelect();
    } catch (error) {
        handleApiError(error);
    }
}

async function handleSalleSubmit(e) {
    e.preventDefault();

    if (!validateForm('salle-form')) {
        showToast('Veuillez remplir tous les champs obligatoires', 'warning');
        return;
    }

    const formData = new FormData(e.target);

    // Mapper les champs frontend vers backend
    const data = {
        name: formData.get('nom'),
        capacity: parseInt(formData.get('capacite')),
        type: formData.get('type'),
        active: true
    };

    try {
        if (currentSalleId) {
            await api.updateRoom(currentSalleId, data);
            showToast('Salle mise à jour avec succès', 'success');
        } else {
            await api.createRoom(data);
            showToast('Salle créée avec succès', 'success');
        }

        closeSalleModal();
        loadSalles();
        loadSallesForSelect();
    } catch (error) {
        handleApiError(error);
    }
}

async function handleSeanceSubmit(e) {
    e.preventDefault();

    if (!validateForm('seance-form')) {
        showToast('Veuillez remplir tous les champs obligatoires', 'warning');
        return;
    }

    const formData = new FormData(e.target);

    // Mapper les champs frontend vers backend
    const data = {
        movie_id: parseInt(formData.get('film_id')),
        room_id: parseInt(formData.get('salle_id')),
        start_time: formData.get('date_seance')
    };

    try {
        if (currentSeanceId) {
            await api.updateScreening(currentSeanceId, data);
            showToast('Séance mise à jour avec succès', 'success');
        } else {
            await api.createScreening(data);
            showToast('Séance créée avec succès', 'success');
        }

        closeSeanceModal();
        loadSeances();
    } catch (error) {
        handleApiError(error);
    }
}

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

    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

    newConfirmBtn.addEventListener('click', function() {
        onConfirm();
        closeConfirmModal();
    });

    modal.classList.add('show');
}

function closeConfirmModal() {
    document.getElementById('confirm-modal').classList.remove('show');
}

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

async function loadFilmForEdit(filmId) {
    try {
        const response = await api.getMovie(filmId);
        const film = response.data;

        document.getElementById('film-titre').value = film.title;
        document.getElementById('film-description').value = film.description || '';
        document.getElementById('film-duree').value = film.duration;
        document.getElementById('film-annee-sortie').value = film.release_year;
        document.getElementById('film-genre-input').value = film.genre || '';
        document.getElementById('film-realisateur').value = film.director || '';
    } catch (error) {
        handleApiError(error);
    }
}

async function loadSalleForEdit(salleId) {
    try {
        const response = await api.getRoom(salleId);
        const salle = response.data;

        document.getElementById('salle-nom').value = salle.name;
        document.getElementById('salle-capacite').value = salle.capacity;
        document.getElementById('salle-type').value = salle.type;
    } catch (error) {
        handleApiError(error);
    }
}

async function loadSeanceForEdit(seanceId) {
    try {
        const response = await api.getScreening(seanceId);
        const seance = response.data;

        document.getElementById('seance-film-id').value = seance.movie_id;
        document.getElementById('seance-salle-id').value = seance.room_id;

        const date = new Date(seance.start_time);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        document.getElementById('seance-date-seance').value = `${year}-${month}-${day}T${hours}:${minutes}`;
    } catch (error) {
        handleApiError(error);
    }
}
