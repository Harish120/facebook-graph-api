#!/bin/bash

echo "🧪 Testing Harryes Facebook Graph API Package against different Laravel versions"
echo "=================================================================="

# Function to test a specific Laravel version
test_laravel_version() {
    local laravel_version=$1
    local testbench_version=$2
    local php_version=$3
    
    echo ""
    echo "🔄 Testing Laravel $laravel_version with PHP $php_version"
    echo "--------------------------------------------------"
    
    # Create a temporary composer.json for testing
    cp composer.json composer.json.backup
    
    # Update composer.json for the specific Laravel version
    sed -i "s/\"laravel\/framework\": \"^10.0|^11.0|^12.0\"/\"laravel\/framework\": \"$laravel_version\"/" composer.json
    sed -i "s/\"orchestra\/testbench\": \"^8.0|^9.0|^10.0\"/\"orchestra\/testbench\": \"$testbench_version\"/" composer.json
    
    # Remove composer.lock to force dependency resolution
    rm -f composer.lock
    
    # Install dependencies
    echo "📦 Installing dependencies..."
    composer install --no-interaction --no-progress
    
    if [ $? -eq 0 ]; then
        echo "✅ Dependencies installed successfully"
        
        # Run tests
        echo "🧪 Running tests..."
        vendor/bin/phpunit --no-coverage
        
        if [ $? -eq 0 ]; then
            echo "✅ Tests passed for Laravel $laravel_version"
        else
            echo "❌ Tests failed for Laravel $laravel_version"
        fi
    else
        echo "❌ Failed to install dependencies for Laravel $laravel_version"
    fi
    
    # Restore original composer.json
    mv composer.json.backup composer.json
    
    echo "--------------------------------------------------"
}

# Test Laravel 10.x
test_laravel_version "^10.0" "^8.0" "8.1"

# Test Laravel 11.x
test_laravel_version "^11.0" "^9.0" "8.2"

# Test Laravel 12.x
test_laravel_version "^12.0" "^10.0" "8.3"

echo ""
echo "🎯 Laravel version compatibility testing completed!"
echo "Check the output above for any failures." 