#!/bin/bash

# Setup script for Todox application

echo "Setting up Todox application..."

# Create database directory
mkdir -p database

# Create MySQL database and user (you may need to adjust these credentials)
echo "Creating database..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS todox;"
mysql -u root -e "CREATE USER IF NOT EXISTS 'todox_user'@'localhost' IDENTIFIED BY 'todox_pass';"
mysql -u root -e "GRANT ALL PRIVILEGES ON todox.* TO 'todox_user'@'localhost';"
mysql -u root -e "FLUSH PRIVILEGES;"

# Update database connection details in connect.php
sed -i "s/define('DB_USER', 'root')/define('DB_USER', 'todox_user')/" todox/api/connect.php
sed -i "s/define('DB_PASS', '')/define('DB_PASS', 'todox_pass')/" todox/api/connect.php
sed -i "s/define('DB_NAME', 'todox')/define('DB_NAME', 'todox')/" todox/api/connect.php

echo "Setup complete!"
echo "To start the application, run: php -S localhost:8000 -t todox/"