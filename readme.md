<h1>Stack de développement Symfony de la SAE3</h1>

--- 
Contenu : 
- [Prérequis](#prérequis)
- [Démarrage](#démarrage)
  - [1. Forker le modèle de stack](#1-forker-le-modèle-de-stack)
  - [2. Cloner la stack du projet](#2-cloner-la-stack-du-projet)
  - [3. Démarrer la stack du projet](#3-démarrer-la-stack-du-projet)
- [Initialiser le service `sfapp`](#initialiser-le-service-sfapp)
- [Partager le projet](#partager-le-projet)

--- 

## Prérequis

Sur votre machine Linux ou Mac :

- Docker 24 
- Docker Engine sous Linux (ne pas installer Docker Desktop sous Linux)
- Docker Desktop sous Mac
- PHPStorm  
  _Votre email étudiant vous permet de bénéficier d'une licence complète de 12 mois pour tous les produits JetBrains_  

De manière optionnelle, mais fortement recommandée :

- Une [clé SSH](https://forge.iut-larochelle.fr/help/ssh/index#generate-an-ssh-key-pair) active sur votre machine
  (perso) et [ajoutée dans votre compte gitlab](https://forge.iut-larochelle.fr/help/ssh/index#add-an-ssh-key-to-your-gitlab-account) :  
  elle vous permettra de ne pas taper votre mot de passe en permanence.

## Démarrage

Démarrage rapide
----------------

Dans un terminal :  
`git clone`

Depuis le terminal dans le dossier de la stack : 
`docker compose up --build -d`

Une fois les containers démarrés, vous pouvez vérifier que php fonctionne :  
`docker exec -it iut-php php -v`

Pour exécuter un script php contenu dans un fichier (par exemple index.php) :  
`docker exec -it iut-php php index.php`

Un shell interactif php est disponible en faisant :  
`docker exec -it iut-php php -a`  

Dans la stack docker dans interactive dans iut-php :  
`composer install`  

- vérifier l'exécution du service `sfapp` a 
```
localhost:8000
```

## Partager le projet

À ce stade, les services `sfapp`, `database` et `nginx` sont créés et démarrés, autrement dit fonctionnels, alors : 
- on fait `commit` et `push` pour partager avec les autres membres de l'équipe
- on déclare tout les membres de l'équipe dans le dépôt du projet avec le rôle `Developer` (si ce n'est pas déjà fait :-))
- chaque membre de l'équipe peut alors 
  - cloner ce nouveau dépôt sur son poste de travail 
  - démarrer toute la stack docker du projet 
