/**
 * Gestion des séances
 */

let currentPageSeances = 1;

/**
 * Charge la liste des séances
 */
async function loadSeances(page = 1) {
    try {
        const search = document.getElementById('seance-search')?.value || '';
        const filmId = document.getElementById('seance-film')?.value || '';
        const salleId = document.getElementById('seance-salle')?.value || '';
        
        const params = {
            page,
            search,
            film_id: filmId,
            salle_id: salleId
        };
        
        const response = await api.getSeances(params);
        const seances = response.data.seances;
        const pagination = response.data.pagination;
        
        renderSeances(seances);
        renderSeancesPagination(pagination);
        currentPageSeances = page;
        
    } catch (error) {
        handleApiError(error);
        emptyElement('seances-tbody');
        const tbody = document.getElementById('seances-tbody');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Erreur lors du chargement des séances</td></tr>';
    }
}

/**
 * Affiche la liste des séances dans le tableau
 */
function renderSeances(seances) {
    const tbody = document.getElementById('seances-tbody');
    emptyElement('seances-tbody');
    
    if (!seances || seances.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Aucune séance trouvée</td></tr>';
        return;
    }
    
    seances.forEach(seance => {
        const tr = document.createElement('tr');
        const dateObj = new Date(seance.date_seance);
        const isPast = dateObj < new Date();
        
        tr.innerHTML = `
            <td>
                <div class="seance-film">
                    <strong>${escapeHtml(seance.film_titre)}</strong>
                    <br><small class="text-muted">${seance.film_duree} min</small>
                </div>
            </td>
            <td>
                <div class="seance-salle">
                    <strong>${escapeHtml(seance.salle_nom)}</strong>
                    <br><small class="text-muted">${seance.salle_type} - ${seance.salle_capacite} places</small>
                </div>
            </td>
            <td>
                <span class="badge badge-date">${formatDate(seance.date_seance)}</span>
            </td>
            <td>
                <span class="badge badge-time">${formatTime(seance.date_seance)}</span>
            </td>
            <td>
                <span class="badge badge-price">${formatPrice(seance.prix)}</span>
            </td>
            <td>
                <div class="table-actions">
                    <button class="btn btn-sm btn-primary" onclick="openSeanceModal(${seance.id})" title="Modifier" ${isPast ? 'disabled' : ''}>
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="confirmDeleteSeance(${seance.id})" title="Supprimer" ${isPast ? 'disabled' : ''}>
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        
        // Ajouter une classe pour les séances passées
        if (isPast) {
            tr.classList.add('seance-past');
            tr.style.opacity = '0.7';
        }
        
        tbody.appendChild(tr);
    });
}

/**
 * Affiche la pagination pour les séances
 */
function renderSeancesPagination(pagination) {
    const container = document.getElementById('seances-pagination');
    emptyElement('seances-pagination');
    
    if (pagination.total_pages <= 1) {
        return;
    }
    
    const { page, total_pages } = pagination;
    
    // Bouton précédent
    const prevBtn = document.createElement('button');
    prevBtn.textContent = 'Précédent';
    prevBtn.disabled = page <= 1;
    prevBtn.onclick = () => loadSeances(page - 1);
    container.appendChild(prevBtn);
    
    // Numéros de page
    const startPage = Math.max(1, page - 2);
    const endPage = Math.min(total_pages, page + 2);
    
    if (startPage > 1) {
        const firstBtn = document.createElement('button');
        firstBtn.textContent = '1';
        firstBtn.onclick = () => loadSeances(1);
        container.appendChild(firstBtn);
        
        if (startPage > 2) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.style.padding = '0 0.5rem';
            container.appendChild(dots);
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.textContent = i;
        pageBtn.classList.toggle('active', i === page);
        pageBtn.onclick = () => loadSeances(i);
        container.appendChild(pageBtn);
    }
    
    if (endPage < total_pages) {
        if (endPage < total_pages - 1) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.style.padding = '0 0.5rem';
            container.appendChild(dots);
        }
        
        const lastBtn = document.createElement('button');
        lastBtn.textContent = total_pages;
        lastBtn.onclick = () => loadSeances(total_pages);
        container.appendChild(lastBtn);
    }
    
    // Bouton suivant
    const nextBtn = document.createElement('button');
    nextBtn.textContent = 'Suivant';
    nextBtn.disabled = page >= total_pages;
    nextBtn.onclick = () => loadSeances(page + 1);
    container.appendChild(nextBtn);
}

/**
 * Charge les filtres pour les séances
 */
async function loadSeanceFilters() {
    try {
        // Les filtres sont déjà chargés via loadFilmsForSelect() et loadSallesForSelect()
        // appelés dans l'initialisation de l'application
    } catch (error) {
        console.error('Error loading seance filters:', error);
    }
}

/**
 * Confirme la suppression d'une séance
 */
function confirmDeleteSeance(seanceId) {
    openConfirmModal(
        'Êtes-vous sûr de vouloir supprimer cette séance ? Cette action est irréversible.',
        async () => {
            try {
                await api.deleteSeance(seanceId);
                showToast('Séance supprimée avec succès', 'success');
                loadSeances(currentPageSeances);
            } catch (error) {
                handleApiError(error);
            }
        }
    );
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
