<?php
/**
 * Contrôleur pour le tableau de bord
 */

require_once __DIR__ . '/../repositories/FilmRepository.php';
require_once __DIR__ . '/../repositories/SalleRepository.php';
require_once __DIR__ . '/../repositories/SeanceRepository.php';

class DashboardController {
    private $filmRepository;
    private $salleRepository;
    private $seanceRepository;
    private $db;

    public function __construct() {
        $this->db = require __DIR__ . '/../config/database.php';
        $this->filmRepository = new FilmRepository($this->db);
        $this->salleRepository = new SalleRepository($this->db);
        $this->seanceRepository = new SeanceRepository($this->db);
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Affiche le tableau de bord avec les statistiques
     */
    public function index() {
        // Récupérer les statistiques
        $stats = [
            'total_films' => $this->filmRepository->count(),
            'nouveaux_films' => $this->filmRepository->countThisMonth(),
            'total_salles' => $this->salleRepository->count(),
            'capacite_totale' => $this->salleRepository->getTotalCapacity(),
            'seances_aujourdhui' => $this->seanceRepository->countToday(),
            'seances_semaine' => $this->seanceRepository->countThisWeek(),
            'seances_a_venir' => $this->seanceRepository->countUpcoming(7)
        ];

        // Récupérer les prochaines séances
        $prochaines_seances = $this->seanceRepository->getUpcoming(5);

        // Inclure header, contenu et footer
        $pageTitle = 'Tableau de bord - My Cinema';
        include __DIR__ . '/../includes/header.php';
        include __DIR__ . '/../views/dashboard.php';
        include __DIR__ . '/../includes/footer.php';
    }
}
