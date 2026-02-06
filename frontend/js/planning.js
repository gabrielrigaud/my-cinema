/**
 * Gestion du planning des séances
 */

/**
 * Charge le planning des séances
 */
async function loadPlanning() {
    try {
        const dateDebut = document.getElementById('planning-date-debut')?.value;
        const dateFin = document.getElementById('planning-date-fin')?.value;
        
        if (!dateDebut || !dateFin) {
            showToast('Veuillez sélectionner une date de début et de fin', 'warning');
            return;
        }
        
        if (new Date(dateDebut) > new Date(dateFin)) {
            showToast('La date de début doit être antérieure à la date de fin', 'warning');
            return;
        }
        
        const params = {
            date_start: dateDebut,
            date_end: dateFin
        };

        const response = await api.getPlanning(params);
        const planning = response.data;
        
        renderPlanning(planning);
        
    } catch (error) {
        handleApiError(error);
        const container = document.getElementById('planning-content');
        container.innerHTML = '<div class="text-center">Erreur lors du chargement du planning</div>';
    }
}

/**
 * Affiche le planning des séances
 */
function renderPlanning(planning) {
    const container = document.getElementById('planning-content');
    emptyElement('planning-content');
    
    if (!planning || planning.length === 0) {
        container.innerHTML = '<div class="text-center">Aucune séance trouvée pour cette période</div>';
        return;
    }
    
    planning.forEach(salle => {
        const salleElement = createSallePlanningElement(salle);
        container.appendChild(salleElement);
    });
}

/**
 * Crée un élément de planning pour une salle
 */
function createSallePlanningElement(salle) {
    const div = document.createElement('div');
    div.className = 'planning-salle';
    
    // En-tête de la salle
    const header = document.createElement('div');
    header.className = 'planning-salle-header';
    header.innerHTML = `
        <i class="fas fa-door-open"></i>
        ${escapeHtml(salle.name)} - ${salle.capacity} places (${escapeHtml(salle.type)})
    `;
    div.appendChild(header);

    // Conteneur des séances
    const seancesContainer = document.createElement('div');
    seancesContainer.className = 'planning-seances';

    if (!salle.screenings || salle.screenings.length === 0) {
        seancesContainer.innerHTML = '<div class="text-center text-muted">Aucune séance programmée</div>';
    } else {
        // Grouper les séances par date
        const seancesByDate = groupSeancesByDate(salle.screenings);
        
        Object.keys(seancesByDate).sort().forEach(date => {
            const dateElement = createDateSeancesElement(date, seancesByDate[date]);
            seancesContainer.appendChild(dateElement);
        });
    }
    
    div.appendChild(seancesContainer);
    return div;
}

/**
 * Crée un élément pour les séances d'une date
 */
function createDateSeancesElement(date, seances) {
    const div = document.createElement('div');
    div.className = 'planning-date-group';
    
    // En-tête de la date
    const dateHeader = document.createElement('div');
    dateHeader.className = 'planning-date-header';
    dateHeader.innerHTML = `
        <i class="fas fa-calendar-day"></i>
        ${formatDate(date)}
    `;
    div.appendChild(dateHeader);
    
    // Liste des séances
    const seancesList = document.createElement('div');
    seancesList.className = 'planning-date-seances';
    
    // Trier les séances par heure
    seances.sort((a, b) => new Date(a.start_time) - new Date(b.start_time));

    seances.forEach(seance => {
        const seanceElement = createSeancePlanningElement(seance);
        seancesList.appendChild(seanceElement);
    });
    
    div.appendChild(seancesList);
    return div;
}

/**
 * Crée un élément pour une séance dans le planning
 */
function createSeancePlanningElement(seance) {
    const div = document.createElement('div');
    div.className = 'planning-seance';

    const dateObj = new Date(seance.start_time);
    const isPast = dateObj < new Date();

    div.innerHTML = `
        <div class="planning-seance-info">
            <div class="planning-seance-film">
                <i class="fas fa-film"></i>
                ${escapeHtml(seance.movie_title)}
                ${isPast ? '<span class="badge badge-past">Passée</span>' : ''}
            </div>
            <div class="planning-seance-time">
                <i class="fas fa-clock"></i>
                ${formatTime(seance.start_time)} - ${calculateEndTime(seance.start_time, seance.movie_duration)}
                <span class="text-muted">(${seance.movie_duration} min)</span>
            </div>
        </div>
    `;
    
    if (isPast) {
        div.classList.add('seance-past');
        div.style.opacity = '0.6';
    }
    
    return div;
}

/**
 * Groupe les séances par date
 */
function groupSeancesByDate(seances) {
    const grouped = {};

    seances.forEach(seance => {
        const date = seance.start_time.split(' ')[0]; // Extraire juste la date
        if (!grouped[date]) {
            grouped[date] = [];
        }
        grouped[date].push(seance);
    });

    return grouped;
}

/**
 * Calcule l'heure de fin d'une séance
 */
function calculateEndTime(dateSeance, duree) {
    const start = new Date(dateSeance);
    const end = new Date(start.getTime() + duree * 60000); // Ajouter la durée en millisecondes
    return formatTime(end);
}

/**
 * Fonctions utilitaires
 */

/**
 * Échappe les caractères HTML pour éviter les injections
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Formate une date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        weekday: 'long',
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

