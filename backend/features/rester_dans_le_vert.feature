# language: fr
Fonctionnalité: Rester dans le vert
  Afin de ne pas finir le mois à découvert
  En tant que personne qui suit ses dépenses
  Je veux être prévenue avant et au moment où je dépasse mon budget mensuel

  Contexte:
    Étant donné un budget de 500 € pour octobre 2026

  Scénario: Une dépense sous le plafond garde le budget dans le vert
    Quand je dépense 120 € pour "Courses" le 5 octobre 2026
    Alors il me reste 380 €
    Et mon budget est "dans le vert"
    Et je n'ai reçu aucune alerte

  Scénario: Approcher du plafond déclenche un avertissement
    Étant donné que j'ai déjà dépensé 300 € pour "Loyer partiel" le 1 octobre 2026
    Quand je dépense 120 € pour "Courses" le 5 octobre 2026
    Alors il me reste 80 €
    Et mon budget est "à surveiller"
    Et j'ai reçu un avertissement

  Scénario: Dépasser le plafond déclenche une alerte
    Quand je dépense 520 € pour "Réparation voiture" le 12 octobre 2026
    Alors je suis à découvert de 20 €
    Et mon budget est "dans le rouge"
    Et j'ai reçu une alerte de dépassement de 20 €

  Scénario: L'alerte de dépassement n'est envoyée qu'une fois par mois
    Étant donné que j'ai déjà dépensé 520 € pour "Réparation voiture" le 12 octobre 2026
    Quand je dépense 30 € pour "Restaurant" le 20 octobre 2026
    Alors je suis à découvert de 50 €
    Et je n'ai reçu aucune nouvelle alerte

  Scénario: Une dépense d'un autre mois est refusée
    Quand je dépense 40 € pour "Cinéma" le 3 novembre 2026
    Alors la dépense est refusée car elle n'appartient pas à octobre 2026
    Et il me reste 500 €
