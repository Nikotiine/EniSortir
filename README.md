# EniSortir

Projet de Groupe pour la formation DWM de l'ENI

Theme du projet  = On va sortir Like (version Campus ENI)

# Techno =>
Symfony 5.4.2
PHP 8.1
ChartUX
Leaflet
EasyAdmin 4
VichUploader


## Installation =>
1. composer install
2. npm install --force
3. creer un .env.dev.local
4. npm run build (web-pack)
5. php bin/console d:d:c
6. php bin/console d:s:c
7. php bin/console make:migration
8. php bin/console d:m:m
9. php bin/console d:f:l
10. symfony server:start

une fois la bdd fonctionelle , un script est a dispo pour reload les fixtures =>
# composer load-fixtures



### pour le .env.dev.local
DATABASE_URL=
MAILER_DSN=gmail+smtp:
URL_API=//url du serveur local de symfony pour consomer l'api 


Projet realiser du 10 au 23 septembre pour la formation DWM de L'eni
Travail demander Iteration 1 , travail fourni iteration 1/2/3 + ajout de fonctionalite valide par la MOA
