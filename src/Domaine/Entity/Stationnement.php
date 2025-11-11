<?php
class Stationnement {
    private $idStationnement;
    private $dateHeureEntree;
    private $dateHeureSortie;
    private $montantFinal;
    private $penaliteAppliquee;
    private $idUtilisateur;
    private $idParking;
    private $idReservation;

    public function __construct($idStationnement, $dateHeureEntree, $dateHeureSortie, $montantFinal, $penaliteAppliquee, $idUtilisateur, $idParking, $idReservation) {
        $this->idStationnement = $idStationnement;
        $this->dateHeureEntree = $dateHeureEntree;
        $this->dateHeureSortie = $dateHeureSortie;
        $this->montantFinal = $montantFinal;
        $this->penaliteAppliquee = $penaliteAppliquee;
        $this->idUtilisateur = $idUtilisateur;
        $this->idParking = $idParking;
        $this->idReservation = $idReservation;
    }

    public function getIdStationnement() {
        return $this->idStationnement;
    }

    public function getDateHeureEntree() {
        return $this->dateHeureEntree;
    }

    public function getDateHeureSortie() {
        return $this->dateHeureSortie;
    }

    public function getMontantFinal() {
        return $this->montantFinal;
    }

    public function getPenaliteAppliquee() {
        return $this->penaliteAppliquee;
    }

    public function getIdUtilisateur() {
        return $this->idUtilisateur;
    }

    public function getIdParking() {
        return $this->idParking;
    }

    public function getIdReservation() {
        return $this->idReservation;
    }
}