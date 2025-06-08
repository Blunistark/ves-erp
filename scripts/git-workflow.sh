#!/bin/bash
# Git Workflow Helper Script for School ERP System
# Usage: ./git-workflow.sh [command]

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "${BLUE}=== $1 ===${NC}"
}

# Check if we're in a git repository
check_git_repo() {
    if ! git rev-parse --git-dir > /dev/null 2>&1; then
        print_error "Not in a git repository!"
        exit 1
    fi
}

# Get current branch
get_current_branch() {
    git branch --show-current
}

# Create a new feature branch
create_feature() {
    local feature_name="$1"
    if [ -z "$feature_name" ]; then
        print_error "Feature name is required!"
        echo "Usage: ./git-workflow.sh feature <feature-name>"
        exit 1
    fi
    
    print_header "Creating Feature Branch"
    print_status "Switching to dev branch..."
    git checkout dev
    
    print_status "Pulling latest changes..."
    git pull origin dev 2>/dev/null || print_warning "Could not pull from origin (remote may not exist)"
    
    print_status "Creating feature branch: feature/$feature_name"
    git checkout -b "feature/$feature_name"
    
    print_status "Feature branch 'feature/$feature_name' created successfully!"
    print_status "You can now start developing your feature."
}

# Create a hotfix branch
create_hotfix() {
    local hotfix_name="$1"
    if [ -z "$hotfix_name" ]; then
        print_error "Hotfix name is required!"
        echo "Usage: ./git-workflow.sh hotfix <hotfix-name>"
        exit 1
    fi
    
    print_header "Creating Hotfix Branch"
    print_status "Switching to main branch..."
    git checkout main
    
    print_status "Pulling latest changes..."
    git pull origin main 2>/dev/null || print_warning "Could not pull from origin (remote may not exist)"
    
    print_status "Creating hotfix branch: hotfix/$hotfix_name"
    git checkout -b "hotfix/$hotfix_name"
    
    print_status "Hotfix branch 'hotfix/$hotfix_name' created successfully!"
    print_status "You can now fix the critical issue."
}

# Merge feature to dev
merge_feature() {
    local current_branch=$(get_current_branch)
    
    if [[ ! $current_branch == feature/* ]]; then
        print_error "You must be on a feature branch to merge!"
        exit 1
    fi
    
    print_header "Merging Feature to Dev"
    print_status "Current feature branch: $current_branch"
    
    print_status "Switching to dev branch..."
    git checkout dev
    
    print_status "Pulling latest dev changes..."
    git pull origin dev 2>/dev/null || print_warning "Could not pull from origin (remote may not exist)"
    
    print_status "Merging $current_branch into dev..."
    git merge "$current_branch" --no-ff
    
    print_status "Feature merged successfully!"
    print_warning "Consider deleting the feature branch: git branch -d $current_branch"
}

# Release dev to main
release_to_main() {
    print_header "Releasing Dev to Main"
    
    print_status "Switching to main branch..."
    git checkout main
    
    print_status "Pulling latest main changes..."
    git pull origin main 2>/dev/null || print_warning "Could not pull from origin (remote may not exist)"
    
    print_status "Merging dev into main..."
    git merge dev --no-ff
    
    print_status "Dev branch merged to main successfully!"
    print_status "Consider creating a release tag."
}

# Show status
show_status() {
    print_header "Git Status"
    
    local current_branch=$(get_current_branch)
    print_status "Current branch: $current_branch"
    
    echo ""
    print_status "Branch list:"
    git branch
    
    echo ""
    print_status "Working directory status:"
    git status --short
    
    echo ""
    print_status "Recent commits:"
    git log --oneline -5
}

# Clean up merged branches
cleanup_branches() {
    print_header "Cleaning Up Merged Branches"
    
    print_status "Switching to dev branch..."
    git checkout dev
    
    print_status "Deleting merged feature branches..."
    git branch --merged | grep "feature/" | xargs -r git branch -d
    
    print_status "Cleanup completed!"
}

# Show help
show_help() {
    echo "School ERP Git Workflow Helper"
    echo ""
    echo "Usage: ./git-workflow.sh [command] [arguments]"
    echo ""
    echo "Commands:"
    echo "  feature <name>    Create a new feature branch from dev"
    echo "  hotfix <name>     Create a new hotfix branch from main"
    echo "  merge-feature     Merge current feature branch to dev"
    echo "  release           Merge dev to main (for releases)"
    echo "  status            Show git status and branch info"
    echo "  cleanup           Delete merged feature branches"
    echo "  help              Show this help message"
    echo ""
    echo "Examples:"
    echo "  ./git-workflow.sh feature add-parent-portal"
    echo "  ./git-workflow.sh hotfix security-patch"
    echo "  ./git-workflow.sh merge-feature"
    echo "  ./git-workflow.sh release"
    echo ""
    echo "Workflow:"
    echo "  1. Create feature branch: ./git-workflow.sh feature <name>"
    echo "  2. Develop your feature and commit changes"
    echo "  3. Merge to dev: ./git-workflow.sh merge-feature"
    echo "  4. Test in dev branch"
    echo "  5. Release to main: ./git-workflow.sh release"
}

# Main script logic
main() {
    check_git_repo
    
    case "${1:-help}" in
        "feature")
            create_feature "$2"
            ;;
        "hotfix")
            create_hotfix "$2"
            ;;
        "merge-feature")
            merge_feature
            ;;
        "release")
            release_to_main
            ;;
        "status")
            show_status
            ;;
        "cleanup")
            cleanup_branches
            ;;
        "help"|*)
            show_help
            ;;
    esac
}

# Run the main function with all arguments
main "$@"
