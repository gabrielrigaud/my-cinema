/**
 * Gestion des films
 */

let currentPageFilms = 1;

/**
 * Charge la liste des films
 */
async function loadFilms(page = 1) {
    try {
        const search = document.getElementById('film-search')?.value || '';
        const genre = document.getElementById('film-genre')?.value || '';
        const annee = document.getElementById('film-annee')?.value || '';
        
        const params = {
            page,
            search,
            genre,
            annee
        };
        
        const response = await api.getMovies(params);
        const films = response.data.movies;
        const pagination = response.data.pagination;
        
        renderFilms(films);
        renderFilmsPagination(pagination);
        currentPageFilms = page;
        
    } catch (error) {
        handleApiError(error);
        emptyElement('films-tbody');
        const tbody = document.getElementById('films-tbody');
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Erreur lors du chargement des films</td></tr>';
    }
}

/**
 * Affiche la liste des films dans le tableau
 */
function renderFilms(films) {
    const tbody = document.getElementById('films-tbody');
    emptyElement('films-tbody');
    
    if (!films || films.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Aucun film trouvé</td></tr>';
        return;
    }
    
    films.forEach(film => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <div class="film-info">
                    <strong>${escapeHtml(film.title)}</strong>
                    ${film.description ? `<br><small class="text-muted">${escapeHtml(truncateText(film.description, 100))}</small>` : ''}
                </div>
            </td>
            <td>
                <span class="badge badge-genre">${escapeHtml(film.genre || '-')}</span>
            </td>
            <td>${film.duration} min</td>
            <td>${film.release_year}</td>
            <td>
                <div class="table-actions">
                    <button class="btn btn-sm btn-primary" onclick="openFilmModal(${film.id})" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="confirmDeleteFilm(${film.id})" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

/**
 * Affiche la pagination pour les films
 */
function renderFilmsPagination(pagination) {
    const container = document.getElementById('films-pagination');
    emptyElement('films-pagination');
    
    if (pagination.total_pages <= 1) {
        return;
    }
    
    const { page, total_pages } = pagination;
    
    // Bouton précédent
    const prevBtn = document.createElement('button');
    prevBtn.textContent = 'Précédent';
    prevBtn.disabled = page <= 1;
    prevBtn.onclick = () => loadFilms(page - 1);
    container.appendChild(prevBtn);
    
    // Numéros de page
    const startPage = Math.max(1, page - 2);
    const endPage = Math.min(total_pages, page + 2);
    
    if (startPage > 1) {
        const firstBtn = document.createElement('button');
        firstBtn.textContent = '1';
        firstBtn.onclick = () => loadFilms(1);
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
        pageBtn.onclick = () => loadFilms(i);
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
        lastBtn.onclick = () => loadFilms(total_pages);
        container.appendChild(lastBtn);
    }
    
    // Bouton suivant
    const nextBtn = document.createElement('button');
    nextBtn.textContent = 'Suivant';
    nextBtn.disabled = page >= total_pages;
    nextBtn.onclick = () => loadFilms(page + 1);
    container.appendChild(nextBtn);
}

/**
 * Charge les filtres pour les films
 */
async function loadFilmFilters() {
    try {
        // Charger les genres
        const genresResponse = await api.getMovieGenres();
        const genres = genresResponse.data;
        const genreSelect = document.getElementById('film-genre');

        if (genreSelect) {
            // Garder l'option "Tous les genres"
            genreSelect.innerHTML = '<option value="">Tous les genres</option>';
            genres.forEach(genre => {
                const option = document.createElement('option');
                option.value = genre;
                option.textContent = genre;
                genreSelect.appendChild(option);
            });
        }

        // Charger les années
        const anneesResponse = await api.getMovieYears();
        const annees = anneesResponse.data;
        const anneeSelect = document.getElementById('film-annee');
        
        if (anneeSelect) {
            // Garder l'option "Toutes les années"
            anneeSelect.innerHTML = '<option value="">Toutes les années</option>';
            annees.forEach(annee => {
                const option = document.createElement('option');
                option.value = annee;
                option.textContent = annee;
                anneeSelect.appendChild(option);
            });
        }
        
    } catch (error) {
        console.error('Error loading film filters:', error);
    }
}

/**
 * Charge les films pour les sélecteurs
 */
async function loadFilmsForSelect() {
    try {
        const response = await api.getMovies({ page: 1, limit: 1000 });
        const films = response.data.movies;

        // Mettre à jour le sélecteur dans le formulaire des séances
        const seanceFilmIdSelect = document.getElementById('seance-film-id');
        if (seanceFilmIdSelect) {
            const defaultValue = seanceFilmIdSelect.value;
            seanceFilmIdSelect.innerHTML = '<option value="">Sélectionner un film</option>';

            films.forEach(film => {
                const option = document.createElement('option');
                option.value = film.id;
                option.textContent = `${film.title} (${film.release_year})`;
                seanceFilmIdSelect.appendChild(option);
            });

            seanceFilmIdSelect.value = defaultValue;
        }

        // Mettre à jour le sélecteur dans les filtres des séances
        const seanceFilmFilterSelect = document.getElementById('seance-film');
        if (seanceFilmFilterSelect) {
            const defaultValue = seanceFilmFilterSelect.value;
            seanceFilmFilterSelect.innerHTML = '<option value="">Tous les films</option>';

            films.forEach(film => {
                const option = document.createElement('option');
                option.value = film.id;
                option.textContent = `${film.title} (${film.release_year})`;
                seanceFilmFilterSelect.appendChild(option);
            });

            seanceFilmFilterSelect.value = defaultValue;
        }

    } catch (error) {
        console.error('Error loading films for select:', error);
    }
}

/**
 * Confirme la suppression d'un film
 */
function confirmDeleteFilm(filmId) {
    openConfirmModal(
        'Êtes-vous sûr de vouloir supprimer ce film ? Cette action est irréversible.',
        async () => {
            try {
                await api.deleteMovie(filmId);
                showToast('Film supprimé avec succès', 'success');
                loadFilms(currentPageFilms);
                loadFilmFilters();
                loadFilmsForSelect();
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

/**
 * Tronque un texte
 */
function truncateText(text, maxLength) {
    if (text.length <= maxLength) {
        return text;
    }
    return text.substring(0, maxLength) + '...';
}
