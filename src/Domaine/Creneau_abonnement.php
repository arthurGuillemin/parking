<?php
class Creneau_abonnement {
    private $idCreneau;
    private $jourSemaineDebut;
    private $heureDebut;
    private $jourSemaineFin;
    private $heureFin;
    private $idTypeAbonnement;

    public function __construct($idCreneau, $jourSemaineDebut, $heureDebut, $jourSemaineFin, $heureFin, $idTypeAbonnement) {
        $this->idCreneau = $idCreneau;
        $this->jourSemaineDebut = $jourSemaineDebut;
        $this->heureDebut = $heureDebut;
        $this->jourSemaineFin = $jourSemaineFin;
        $this->heureFin = $heureFin;
        $this->idTypeAbonnement = $idTypeAbonnement;
    }

    public function getIdCreneau() {
        return $this->idCreneau;
    }

    public function getJourSemaineDebut() {
        return $this->jourSemaineDebut;
    }

    public function getHeureDebut() {
        return $this->heureDebut;
    }

    public function getJourSemaineFin() {
        return $this->jourSemaineFin;
    }

    public function getHeureFin() {
        return $this->heureFin;
    }

    public function getIdTypeAbonnement() {
        return $this->idTypeAbonnement;
    }
}