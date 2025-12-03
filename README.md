Projet : Système de Parking Partagé

Hetic – 3ᵉ année – 2025

Contexte

Avec le dérèglement climatique, la réduction des émissions de gaz à effet de serre devient un enjeu mondial, et les transports en représentent une part significative.

Selon certaines études, la recherche d’une place de parking représente environ 30 % du trafic urbain, entraînant embouteillages, émissions supplémentaires et perte de temps.

Pourtant, de nombreuses places existent mais restent inoccupées selon les horaires :

Places libres en journée dans les immeubles d’habitation et hôtels

Places libres la nuit et le week-end dans les entreprises

L’objectif est donc de créer une solution de parking partagé permettant aux propriétaires de louer leurs places inoccupées. Les utilisateurs peuvent consulter, réserver et payer une place via une application web.

Objectif

Développer une application web en PHP respectant les principes de la Clean Architecture, avec :

Toutes les fonctionnalités du document

Une architecture propre et modulable

Des tests (PHPUnit)

Un système d’authentification sécurisé (JWT)

La partie matérielle (ouverture de portes) est simulée : une entrée/sortie s’enregistre via des endpoints.

Technologies à utiliser

PHP 8.x, sans framework (Laravel, Symfony, etc.)

PHPUnit pour les tests

Composer et des librairies externes autorisées

Framework JS côté client autorisé (React, Vue, Angular)

Données du système
Parking

Coordonnées GPS

Nombre de places

Tarif horaire (peut varier)

Horaires d’ouverture

Liste des réservations

Liste des stationnements

Facturation par tranche de 15 minutes.

Utilisateur

email

password

nom

prénom

réservations

stationnements

Propriétaire de parking

email

password

nom

prénom

liste de parkings possédés

Stationnement

Un stationnement représente l’intervalle entre entrée et sortie.
Contient :

utilisateur

début (timestamp)

fin (timestamp)

parking

Réservation

utilisateur

parking

début (timestamp)

fin (timestamp)

Abonnement

Un abonnement garantit une place sur des créneaux hebdomadaires.
Durée : minimum 1 mois, jusqu’à 1 an.

Types possibles :

Total : accès illimité

Week-end : Vendredi 18h → Lundi 10h

Spécifique : Jeudi 10h → Vendredi 10h

Soir : tous les soirs 18h → 8h

Données :

utilisateur

parking

créneaux horaires réservés

Les données fournies sont le minimum obligatoire.

Fonctionnalités
Clean Architecture

Le projet doit comporter au minimum :

Domaine

Use Case

External interface / View

Les règles métiers ne doivent dépendre ni des contrôleurs, ni de la base de données, ni d’une technologie externe.

Use cases à implémenter
Espace Propriétaire

Créer compte + authentification

Ajouter un parking

Modifier tarifs

Modifier horaires

Voir réservations du parking

Voir stationnements du parking

Voir nombre de places disponibles à un instant donné

Calcul du chiffre d’affaires mensuel (réservations + abonnements)

Ajouter un type d’abonnement

Voir les conducteurs garés hors créneaux autorisés

Espace Utilisateur

Créer compte + authentification

Rechercher parkings disponibles autour de coordonnées GPS

Voir infos d’un parking

Réserver une place

Voir abonnements d’un parking

Souscrire un abonnement

Entrer dans un parking

Sortir d’un parking

Voir ses stationnements

Voir ses réservations

Obtenir la facture d’une réservation

Gestion des places disponibles

Une réservation occupe une place sur tout son créneau

Fin de réservation → la place est libérée

Un abonnement occupe une place sur son créneau même si l’utilisateur n’est pas physiquement présent

Entrées et sorties

Entrée autorisée uniquement avec réservation active ou abonnement actif

Réservation refusée si le parking est plein sur une partie du créneau

L’utilisateur est facturé intégralement même s’il n’utilise pas toute sa réservation

Le système enregistre :

heure d’entrée

heure de sortie

libération automatique de la place

Horaires d’ouverture

Un parking peut être :

toujours ouvert

ouvert sur des plages précises
(ex : week-end, soirées, journées spécifiques)

Les réservations actives occupent une place même si l’utilisateur n’est pas présent.

Pénalités

Si un conducteur dépasse son créneau :

+20 € de pénalité

durée réelle facturée

Exemple : réservation 3h → stationnement 4h
→ facturation 4h + 20 €.

Les dépassements empêchent de nouvelles réservations si le parking est ainsi rempli.

Prix d’une réservation

Dépend de la grille tarifaire

Tarifs modulables par tranche de 15 minutes

Après la sortie, le système calcule le prix final et génère une facture (HTML ou PDF)

Stockage des données

Deux systèmes nécessaires, interchangeables :

base relationnelle (MySQL, PostgreSQL, SQLite)

base NoSQL ou fichier

Aucune modification ne doit être nécessaire dans les Entités ou Use cases.

Frontend

Deux modes obligatoires :

1. Interface HTML

Pour :

visualiser les parkings

réserver

consulter abonnements

gérer compte

2. API REST (JSON)

GET, POST, PUT, DELETE

Utilisable par un client JS ou autre application

Totalement indépendante des Use Cases et Entités

Authentification

JWT

Hashage des mots de passe en PHP

Protection : anti-SQL injection, anti-XSS

Gestion du cycle de vie des tokens

Tests

PHPUnit obligatoire

60 % de couverture minimum pour Domaine + Entités

4 tests fonctionnels min. :

2 côté utilisateur

2 côté propriétaire

Critères :

pertinence

couverture des règles métiers

Critères d’évaluation

Fonctionnalités implémentées

Qualité de l’architecture

Authentification JWT

Exhaustivité des tests

Qualité du code (clarté, cohérence, respect conventions, fonctions < 20 lignes)

Barème

Fonctionnalités : 12 points

Tests PHPUnit : 4 points

Authentification JWT : 2 points

Architecture : 2 points