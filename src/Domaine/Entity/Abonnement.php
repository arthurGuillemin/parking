<?php
class Abonnement {
    private $id;
    private $dateDebut;
    private $dateFin;
    private $prixMensuel;
    private $statut;
    private $idUtilisateur;
    private $idParking;
    private $idTypeAbonnement;

    public function __construct($id, $dateDebut, $dateFin, $prixMensuel, $statut, $idUtilisateur, $idParking, $idTypeAbonnement) {
        $this->id = $id;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->prixMensuel = $prixMensuel;
        $this->statut = $statut;
        $this->idUtilisateur = $idUtilisateur;
        $this->idParking = $idParking;
        $this->idTypeAbonnement = $idTypeAbonnement;
    }

    public function getId() {
        return $this->id;
    }

    public function getDateDebut() {
        return $this->dateDebut;
    }

    public function getDateFin() {
        return $this->dateFin;
    }

    public function getPrixMensuel() {
        return $this->prixMensuel;
    }

    public function getStatut() {
        return $this->statut;
    }

    public function getIdUtilisateur() {
        return $this->idUtilisateur;
    }

    public function getIdParking() {
        return $this->idParking;
    }

    public function getIdTypeAbonnement() {
        return $this->idTypeAbonnement;
    }
}