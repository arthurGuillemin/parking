<?php
class Type_abonnement {
    private $id;
    private $nom;
    private $description;

    public function __construct($id, $nom, $description) {
        $this->id = $id;
        $this->nom = $nom;
        $this->description = $description;
    }

    public function getId() {
        return $this->id;
    }

    public function getNom() {
        return $this->nom;
    }

    public function getDescription() {
        return $this->description;
    }
}