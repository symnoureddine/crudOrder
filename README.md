# Create database 
php bin/console console doctrine:database:create

# update schema database 
php bin/console console doctrine:schema:u --force

# Create orders from file xml

php bin/console console app:create-orders-from-xml

# Start the internal web server

php bin/console server:start