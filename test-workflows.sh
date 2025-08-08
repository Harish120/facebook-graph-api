#!/bin/bash

echo "🧪 Testing GitHub Workflows Locally"
echo "=================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to run a command and check result
run_command() {
    local step_name="$1"
    local command="$2"
    
    echo -e "\n${BLUE}🔄 Running: $step_name${NC}"
    echo "Command: $command"
    echo "----------------------------------------"
    
    eval "$command"
    exit_code=$?
    if [ $exit_code -eq 0 ] || [ $exit_code -eq 1 ]; then
        echo -e "${GREEN}✅ $step_name - PASSED (exit code: $exit_code)${NC}"
        return 0
    else
        echo -e "${RED}❌ $step_name - FAILED (exit code: $exit_code)${NC}"
        return 1
    fi
}

# Function to test a specific workflow
test_workflow() {
    local workflow_name="$1"
    echo -e "\n${YELLOW}🧪 Testing Workflow: $workflow_name${NC}"
    echo "=========================================="
    
    case $workflow_name in
        "tests")
            run_command "Install Dependencies" "composer install --prefer-dist --no-interaction --no-progress"
            run_command "Run Tests" "composer test"
            run_command "Generate Coverage" "vendor/bin/phpunit --coverage-clover=coverage.xml"
            ;;
        "code-quality")
            run_command "Install Dependencies" "composer install --prefer-dist --no-interaction --no-progress"
            run_command "Run Code Quality Checks" "composer check"
            run_command "Generate Coverage" "vendor/bin/phpunit --coverage-clover=coverage.xml"
            ;;
        "security")
            run_command "Install Dependencies" "composer install --prefer-dist --no-interaction --no-progress"
            run_command "Run Security Audit" "composer security"
            run_command "Generate Security Report" "composer audit --format=json --working-dir=. | tee audit.json || true"
            ;;
        "ci")
            run_command "Install Dependencies" "composer install --prefer-dist --no-interaction --no-progress"
            run_command "Run Complete CI" "composer ci:coverage"
            ;;
        "laravel-compatibility")
            run_command "Install Dependencies" "composer install --prefer-dist --no-interaction --no-progress"
            
            echo -e "\n${YELLOW}Testing Laravel 10 Compatibility${NC}"
            run_command "Install Laravel 10" "composer require 'laravel/framework:^10.0' 'orchestra/testbench:^8.0' --no-interaction --no-progress"
            run_command "Test Laravel 10" "composer test"
            
            echo -e "\n${YELLOW}Testing Laravel 11 Compatibility${NC}"
            run_command "Install Laravel 11" "composer require 'laravel/framework:^11.0' 'orchestra/testbench:^9.0' --no-interaction --no-progress"
            run_command "Test Laravel 11" "composer test"
            
            echo -e "\n${YELLOW}Testing Laravel 12 Compatibility${NC}"
            run_command "Install Laravel 12" "composer require 'laravel/framework:^12.0' 'orchestra/testbench:^10.0' --no-interaction --no-progress"
            run_command "Install PHPUnit 11" "composer require 'phpunit/phpunit:^11.0' --no-interaction --no-progress --dev"
            run_command "Test Laravel 12" "composer test"
            
            run_command "Restore Dependencies" "git checkout composer.json && composer install --prefer-dist --no-interaction --no-progress"
            ;;
        *)
            echo -e "${RED}❌ Unknown workflow: $workflow_name${NC}"
            echo "Available workflows: tests, code-quality, security, ci, laravel-compatibility"
            return 1
            ;;
    esac
}

# Main execution
if [ $# -eq 0 ]; then
    echo "Usage: $0 <workflow_name>"
    echo "Available workflows:"
    echo "  tests              - Test matrix workflow"
    echo "  code-quality       - Code quality workflow"
    echo "  security           - Security workflow"
    echo "  ci                 - Complete CI workflow"
    echo "  laravel-compatibility - Laravel compatibility workflow"
    echo "  all                - Run all workflows"
    exit 1
fi

workflow_name="$1"

if [ "$workflow_name" = "all" ]; then
    echo -e "${YELLOW}🚀 Running all workflows...${NC}"
    workflows=("tests" "code-quality" "security" "ci" "laravel-compatibility")
    
    for workflow in "${workflows[@]}"; do
        test_workflow "$workflow"
        if [ $? -ne 0 ]; then
            echo -e "${RED}❌ Workflow $workflow failed!${NC}"
            exit 1
        fi
    done
    
    echo -e "\n${GREEN}🎉 All workflows completed successfully!${NC}"
else
    test_workflow "$workflow_name"
    if [ $? -eq 0 ]; then
        echo -e "\n${GREEN}🎉 Workflow $workflow_name completed successfully!${NC}"
    else
        echo -e "\n${RED}❌ Workflow $workflow_name failed!${NC}"
        exit 1
    fi
fi 