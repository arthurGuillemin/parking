<?php
class Reservation {
    private $idReservation;
    private $dateHeureDebut;
    private $dateHeureFin;
    private $statut;
    private $montantCalcule;
    private $montantFinal;
    private $idUtilisateur;
    private $idParking;

    public function __construct($idReservation, $dateHeureDebut, $dateHeureFin, $statut, $montantCalcule, $montantFinal, $idUtilisateur, $idParking) {
        $this->idReservation = $idReservation;
        $this->dateHeureDebut = $dateHeureDebut;
        $this->dateHeureFin = $dateHeureFin;
        $this->statut = $statut;
        $this->montantCalcule = $montantCalcule;
        $this->montantFinal = $montantFinal;
        $this->idUtilisateur = $idUtilisateur;
        $this->idParking = $idParking;
    }

    public function getIdReservation() {
        return $this->idReservation;
    }

    public function getDateHeureDebut() {
        return $this->dateHeureDebut;
    }

    public function getDateHeureFin() {
        return $this->dateHeureFin;
    }

    public function getStatut() {
        return $this->statut;
    }

    public function getMontantCalcule() {
        return $this->montantCalcule;
    }

    public function getMontantFinal() {
        return $this->montantFinal;
    }

    public function getIdUtilisateur() {
        return $this->idUtilisateur;
    }

    public function getIdParking() {
        return $this->idParking;
    }
}