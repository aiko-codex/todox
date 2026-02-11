#!/bin/bash

# Fail fast on errors
set -e

echo "Setting up project directory structure..."

# Create the complete directory hierarchy
mkdir -p src/{api,database,includes,assets/css,assets/js}

echo "Directories created successfully."

# Set appropriate permissions on database directory
chmod 775 src/database

echo "Permissions set on database directory."

# Create .gitkeep files to ensure Git tracks empty directories
touch src/api/.gitkeep \
      src/database/.gitkeep \
      src/includes/.gitkeep \
      src/assets/css/.gitkeep \
      src/assets/js/.gitkeep

echo "Placeholder files created for Git tracking."

echo "Setup complete! Directory structure and permissions configured."
echo ""
echo "Next steps:"
echo "1. Start the development server: php -S localhost:8000"
echo "2. Access the application in your browser at http://localhost:8000"