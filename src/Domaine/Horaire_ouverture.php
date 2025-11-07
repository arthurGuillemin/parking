<?php
class Horaire_ouverture {
    private $idHoraire;
    private $jourSemaine;
    private $heureOuverture;
    private $heureFermeture;
    private $parkingId;

    public function __construct($idHoraire, $jourSemaine, $heureOuverture, $heureFermeture, $parkingId) {
        $this->idHoraire = $idHoraire;
        $this->jourSemaine = $jourSemaine;
        $this->heureOuverture = $heureOuverture;
        $this->heureFermeture = $heureFermeture;
        $this->parkingId = $parkingId;
    }

    public function getIdHoraire() {
        return $this->idHoraire;
    }

    public function getJourSemaine() {
        return $this->jourSemaine;
    }

    public function getHeureOuverture() {
        return $this->heureOuverture;
    }

    public function getHeureFermeture() {
        return $this->heureFermeture;
    }

    public function getParkingId() {
        return $this->parkingId;
    }
}