1 - Il faut exécuter composer install sur le code téléchargé

2 - Il faut modifier le fichier .env.local.php avec les bons accès de base de données <DB_USERNAME>, <DB_PASSWORD>, <DB_HOST>, <DB_NAME>

3 - Il faut exécuter via bin/console : doctrine:database:create puis make:migration puis doctrine:migrations:migrate afin de créer la base de données

4 - Le lien d'initialisation est /init

5 - La durée de la session admin est de 10 minutes

6 - Cette session n'utilise pas la classe User ni le composant Security.
