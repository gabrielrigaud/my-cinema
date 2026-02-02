/**
 * Gestion des salles
 */

let currentPageSalles = 1;

/**
 * Charge la liste des salles
 */
async function loadSalles(page = 1) {
    try {
        const search = document.getElementById('salle-search')?.value || '';
        
        const params = {
            page,
            search
        };
        
        const response = await api.getSalles(params);
        const salles = response.data.salles;
        const pagination = response.data.pagination;
        
        renderSalles(salles);
        renderSallesPagination(pagination);
        currentPageSalles = page;
        
    } catch (error) {
        handleApiError(error);
        emptyElement('salles-tbody');
        const tbody = document.getElementById('salles-tbody');
        tbody.innerHTML = '<tr><td colspan="4" class="text-center">Erreur lors du chargement des salles</td></tr>';
    }
}

/**
 * Affiche la liste des salles dans le tableau
 */
function renderSalles(salles) {
    const tbody = document.getElementById('salles-tbody');
    emptyElement('salles-tbody');
    
    if (!salles || salles.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center">Aucune salle trouvée</td></tr>';
        return;
    }
    
    salles.forEach(salle => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <div class="salle-info">
                    <strong>${escapeHtml(salle.nom)}</strong>
                </div>
            </td>
            <td>
                <span class="badge badge-capacity">${salle.capacite} places</span>
            </td>
            <td>
                <span class="badge badge-type">${escapeHtml(salle.type)}</span>
            </td>
            <td>
                <div class="table-actions">
                    <button class="btn btn-sm btn-primary" onclick="openSalleModal(${salle.id})" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="confirmDeleteSalle(${salle.id})" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

/**
 * Affiche la pagination pour les salles
 */
function renderSallesPagination(pagination) {
    const container = document.getElementById('salles-pagination');
    emptyElement('salles-pagination');
    
    if (pagination.total_pages <= 1) {
        return;
    }
    
    const { page, total_pages } = pagination;
    
    // Bouton précédent
    const prevBtn = document.createElement('button');
    prevBtn.textContent = 'Précédent';
    prevBtn.disabled = page <= 1;
    prevBtn.onclick = () => loadSalles(page - 1);
    container.appendChild(prevBtn);
    
    // Numéros de page
    const startPage = Math.max(1, page - 2);
    const endPage = Math.min(total_pages, page + 2);
    
    if (startPage > 1) {
        const firstBtn = document.createElement('button');
        firstBtn.textContent = '1';
        firstBtn.onclick = () => loadSalles(1);
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
        pageBtn.onclick = () => loadSalles(i);
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
        lastBtn.onclick = () => loadSalles(total_pages);
        container.appendChild(lastBtn);
    }
    
    // Bouton suivant
    const nextBtn = document.createElement('button');
    nextBtn.textContent = 'Suivant';
    nextBtn.disabled = page >= total_pages;
    nextBtn.onclick = () => loadSalles(page + 1);
    container.appendChild(nextBtn);
}

/**
 * Charge les salles pour les sélecteurs
 */
async function loadSallesForSelect() {
    try {
        const response = await api.getSallesForSelect();
        const salles = response.data;
        
        // Mettre à jour le sélecteur dans le formulaire des séances
        const seanceSalleSelect = document.getElementById('seance-salle-id');
        if (seanceSalleSelect) {
            // Garder l'option par défaut
            const defaultValue = seanceSalleSelect.value;
            seanceSalleSelect.innerHTML = '<option value="">Sélectionner une salle</option>';
            
            salles.forEach(salle => {
                const option = document.createElement('option');
                option.value = salle.id;
                option.textContent = `${salle.nom} (${salle.capacite} places, ${salle.type})`;
                seanceSalleSelect.appendChild(option);
            });
            
            // Restaurer la valeur précédente
            seanceSalleSelect.value = defaultValue;
        }
        
        // Mettre à jour le sélecteur dans les filtres des séances
        const seanceFilterSalleSelect = document.getElementById('seance-salle');
        if (seanceFilterSalleSelect) {
            const defaultValue = seanceFilterSalleSelect.value;
            seanceFilterSalleSelect.innerHTML = '<option value="">Toutes les salles</option>';
            
            salles.forEach(salle => {
                const option = document.createElement('option');
                option.value = salle.id;
                option.textContent = `${salle.nom} (${salle.capacite} places)`;
                seanceFilterSalleSelect.appendChild(option);
            });
            
            seanceFilterSalleSelect.value = defaultValue;
        }
        
    } catch (error) {
        console.error('Error loading salles for select:', error);
    }
}

/**
 * Confirme la suppression d'une salle
 */
function confirmDeleteSalle(salleId) {
    openConfirmModal(
        'Êtes-vous sûr de vouloir supprimer cette salle ? Cette action est irréversible.',
        async () => {
            try {
                await api.deleteSalle(salleId);
                showToast('Salle supprimée avec succès', 'success');
                loadSalles(currentPageSalles);
                loadSallesForSelect();
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
