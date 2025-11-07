<?php
class Regle_tarifaire {
    private $idRegle;
    private $dureeDebutMinute;
    private $dureeFinMinute;
    private $prixParTranche;
    private $trancheMinute;
    private $dateApplication;
    private $idParking;

    public function __construct($idRegle, $dureeDebutMinute, $dureeFinMinute, $prixParTranche, $trancheMinute, $dateApplication, $idParking) {
        $this->idRegle = $idRegle;
        $this->dureeDebutMinute = $dureeDebutMinute;
        $this->dureeFinMinute = $dureeFinMinute;
        $this->prixParTranche = $prixParTranche;
        $this->trancheMinute = $trancheMinute;
        $this->dateApplication = $dateApplication;
        $this->idParking = $idParking;
    }

    public function getIdRegle() {
        return $this->idRegle;
    }

    public function getDureeDebutMinute() {
        return $this->dureeDebutMinute;
    }

    public function getDureeFinMinute() {
        return $this->dureeFinMinute;
    }

    public function getPrixParTranche() {
        return $this->prixParTranche;
    }

    public function getTrancheMinute() {
        return $this->trancheMinute;
    }

    public function getDateApplication() {
        return $this->dateApplication;
    }

    public function getIdParking() {
        return $this->idParking;
    }
}