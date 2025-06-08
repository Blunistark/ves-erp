# Git Workflow Helper Script for School ERP System (PowerShell)
# Usage: .\git-workflow.ps1 [command] [arguments]

param(
    [Parameter(Position=0)]
    [string]$Command = "help",
    
    [Parameter(Position=1)]
    [string]$Name
)

# Function to print colored output
function Write-Status {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "[WARNING] $Message" -ForegroundColor Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "[ERROR] $Message" -ForegroundColor Red
}

function Write-Header {
    param([string]$Message)
    Write-Host "=== $Message ===" -ForegroundColor Blue
}

# Check if we're in a git repository
function Test-GitRepo {
    try {
        git rev-parse --git-dir | Out-Null
        return $true
    }
    catch {
        Write-Error "Not in a git repository!"
        exit 1
    }
}

# Get current branch
function Get-CurrentBranch {
    return git branch --show-current
}

# Create a new feature branch
function New-FeatureBranch {
    param([string]$FeatureName)
    
    if ([string]::IsNullOrEmpty($FeatureName)) {
        Write-Error "Feature name is required!"
        Write-Host "Usage: .\git-workflow.ps1 feature <feature-name>"
        exit 1
    }
    
    Write-Header "Creating Feature Branch"
    Write-Status "Switching to dev branch..."
    git checkout dev
    
    Write-Status "Pulling latest changes..."
    try {
        git pull origin dev
    }
    catch {
        Write-Warning "Could not pull from origin (remote may not exist)"
    }
    
    Write-Status "Creating feature branch: feature/$FeatureName"
    git checkout -b "feature/$FeatureName"
    
    Write-Status "Feature branch 'feature/$FeatureName' created successfully!"
    Write-Status "You can now start developing your feature."
}

# Create a hotfix branch
function New-HotfixBranch {
    param([string]$HotfixName)
    
    if ([string]::IsNullOrEmpty($HotfixName)) {
        Write-Error "Hotfix name is required!"
        Write-Host "Usage: .\git-workflow.ps1 hotfix <hotfix-name>"
        exit 1
    }
    
    Write-Header "Creating Hotfix Branch"
    Write-Status "Switching to main branch..."
    git checkout main
    
    Write-Status "Pulling latest changes..."
    try {
        git pull origin main
    }
    catch {
        Write-Warning "Could not pull from origin (remote may not exist)"
    }
    
    Write-Status "Creating hotfix branch: hotfix/$HotfixName"
    git checkout -b "hotfix/$HotfixName"
    
    Write-Status "Hotfix branch 'hotfix/$HotfixName' created successfully!"
    Write-Status "You can now fix the critical issue."
}

# Merge feature to dev
function Merge-FeatureToDev {
    $currentBranch = Get-CurrentBranch
    
    if (-not $currentBranch.StartsWith("feature/")) {
        Write-Error "You must be on a feature branch to merge!"
        exit 1
    }
    
    Write-Header "Merging Feature to Dev"
    Write-Status "Current feature branch: $currentBranch"
    
    Write-Status "Switching to dev branch..."
    git checkout dev
    
    Write-Status "Pulling latest dev changes..."
    try {
        git pull origin dev
    }
    catch {
        Write-Warning "Could not pull from origin (remote may not exist)"
    }
    
    Write-Status "Merging $currentBranch into dev..."
    git merge $currentBranch --no-ff
    
    Write-Status "Feature merged successfully!"
    Write-Warning "Consider deleting the feature branch: git branch -d $currentBranch"
}

# Release dev to main
function Invoke-Release {
    Write-Header "Releasing Dev to Main"
    
    Write-Status "Switching to main branch..."
    git checkout main
    
    Write-Status "Pulling latest main changes..."
    try {
        git pull origin main
    }
    catch {
        Write-Warning "Could not pull from origin (remote may not exist)"
    }
    
    Write-Status "Merging dev into main..."
    git merge dev --no-ff
    
    Write-Status "Dev branch merged to main successfully!"
    Write-Status "Consider creating a release tag."
}

# Show status
function Show-GitStatus {
    Write-Header "Git Status"
    
    $currentBranch = Get-CurrentBranch
    Write-Status "Current branch: $currentBranch"
    
    Write-Host ""
    Write-Status "Branch list:"
    git branch
    
    Write-Host ""
    Write-Status "Working directory status:"
    git status --short
    
    Write-Host ""
    Write-Status "Recent commits:"
    git log --oneline -5
}

# Clean up merged branches
function Remove-MergedBranches {
    Write-Header "Cleaning Up Merged Branches"
    
    Write-Status "Switching to dev branch..."
    git checkout dev
    
    Write-Status "Finding merged feature branches..."
    $mergedBranches = git branch --merged | Where-Object { $_ -like "*feature/*" }
    
    if ($mergedBranches) {
        Write-Status "Deleting merged feature branches..."
        foreach ($branch in $mergedBranches) {
            $branchName = $branch.Trim()
            git branch -d $branchName
        }
    } else {
        Write-Status "No merged feature branches found."
    }
    
    Write-Status "Cleanup completed!"
}

# Show help
function Show-Help {
    Write-Host "School ERP Git Workflow Helper (PowerShell)" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Usage: .\git-workflow.ps1 [command] [arguments]"
    Write-Host ""
    Write-Host "Commands:"
    Write-Host "  feature <name>    Create a new feature branch from dev"
    Write-Host "  hotfix <name>     Create a new hotfix branch from main"
    Write-Host "  merge-feature     Merge current feature branch to dev"
    Write-Host "  release           Merge dev to main (for releases)"
    Write-Host "  status            Show git status and branch info"
    Write-Host "  cleanup           Delete merged feature branches"
    Write-Host "  help              Show this help message"
    Write-Host ""
    Write-Host "Examples:"
    Write-Host "  .\git-workflow.ps1 feature add-parent-portal"
    Write-Host "  .\git-workflow.ps1 hotfix security-patch"
    Write-Host "  .\git-workflow.ps1 merge-feature"
    Write-Host "  .\git-workflow.ps1 release"
    Write-Host ""
    Write-Host "Workflow:"
    Write-Host "  1. Create feature branch: .\git-workflow.ps1 feature <name>"
    Write-Host "  2. Develop your feature and commit changes"
    Write-Host "  3. Merge to dev: .\git-workflow.ps1 merge-feature"
    Write-Host "  4. Test in dev branch"
    Write-Host "  5. Release to main: .\git-workflow.ps1 release"
}

# Main script logic
function Main {
    Test-GitRepo
    
    switch ($Command.ToLower()) {
        "feature" {
            New-FeatureBranch -FeatureName $Name
        }
        "hotfix" {
            New-HotfixBranch -HotfixName $Name
        }
        "merge-feature" {
            Merge-FeatureToDev
        }
        "release" {
            Invoke-Release
        }
        "status" {
            Show-GitStatus
        }
        "cleanup" {
            Remove-MergedBranches
        }
        default {
            Show-Help
        }
    }
}

# Run the main function
Main
