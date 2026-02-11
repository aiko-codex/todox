```bash
#!/bin/bash

# Todox Setup Script
# Creates database and initializes schema

echo "Setting up Todox application..."

# Create database directory if it doesn't exist
mkdir -p database

# Create MySQL database and table
echo "Creating MySQL database and tables..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS todox;"
mysql -u root todox < database/schema.sql

echo "Setup complete!"
echo "To start the application, run: php -S localhost:8000"
```