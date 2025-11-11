<?php
class Facture {
    private $idFacture;
    private $dateEmission;
    private $montantHT;
    private $montantTTC;
    private $detailJson;
    private $typeFacture;
    private $idReservation;
    private $idStationnement;

    public function __construct($idFacture, $dateEmission, $montantHT, $montantTTC, $detailJson, $typeFacture, $idReservation = null, $idStationnement = null) {
        $this->idFacture = $idFacture;
        $this->dateEmission = $dateEmission;
        $this->montantHT = $montantHT;
        $this->montantTTC = $montantTTC;
        $this->detailJson = $detailJson;
        $this->typeFacture = $typeFacture;
        $this->idReservation = $idReservation;
        $this->idStationnement = $idStationnement;
    }

    public function getIdFacture() {
        return $this->idFacture;
    }

    public function getDateEmission() {
        return $this->dateEmission;
    }

    public function getMontantHT() {
        return $this->montantHT;
    }

    public function getMontantTTC() {
        return $this->montantTTC;
    }

    public function getDetailJson() {
        return $this->detailJson;
    }

    public function getTypeFacture() {
        return $this->typeFacture;
    }

    public function getIdReservation() {
        return $this->idReservation;
    }

    public function getIdStationnement() {
        return $this->idStationnement;
    }
}