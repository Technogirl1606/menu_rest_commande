<?php

namespace App;

enum OrderStatus: string
{
  case Nouvelle = 'nouvelle';
    case Preparation = 'preparation';
    case Prete = 'prete';
    case Servie = 'servie';
    case Annulee = 'annulee';
 
    public function label(): string
    {
        return match ($this) {
            self::Nouvelle => 'Nouvelle',
            self::Preparation => 'En préparation',
            self::Prete => 'Prête',
            self::Servie => 'Servie',
            self::Annulee => 'Annulée',
        };
    }
 
    /**
     * Statut suivant dans le flux cuisine (utile pour le bouton "avancer").
     * Retourne null si c'est déjà un état final.
     */
    public function next(): ?self
    {
        return match ($this) {
            self::Nouvelle => self::Preparation,
            self::Preparation => self::Prete,
            self::Prete => self::Servie,
            default => null,
        };
    }  
}
