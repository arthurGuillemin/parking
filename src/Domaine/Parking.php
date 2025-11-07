<?php
class Parking {
    private $id;
    private $adresse;
    private $latitude;
    private $longitude;
    private $capacite;
    private $proprietaireId;
    private $ouvert_24_7;

    public function __construct($id, $adresse, $latitude, $longitude, $capacite, $proprietaireId, $ouvert_24_7) {
        $this->id = $id;
        $this->adresse = $adresse;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->capacite = $capacite;
        $this->proprietaireId = $proprietaireId;
        $this->ouvert_24_7 = $ouvert_24_7;
    }

    public function getId() {
        return $this->id;
    }

    public function getAdresse() {
        return $this->adresse;
    }

    public function getLatitude() {
        return $this->latitude;
    }

    public function getLongitude() {
        return $this->longitude;
    }

    public function getCapacite() {
        return $this->capacite;
    }

    public function getProprietaireId() {
        return $this->proprietaireId;
    }

    public function isOuvert247() {
        return $this->ouvert_24_7;
    }
}