# School ERP Git Hooks Setup
# This script sets up git hooks for commit message validation and pre-commit checks

param(
    [switch]$Install,
    [switch]$Uninstall,
    [switch]$Help
)

function Show-Help {
    Write-Host "School ERP Git Hooks Setup" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Usage: .\setup-git-hooks.ps1 [options]"
    Write-Host ""
    Write-Host "Options:"
    Write-Host "  -Install    Install git hooks"
    Write-Host "  -Uninstall  Remove git hooks"
    Write-Host "  -Help       Show this help message"
    Write-Host ""
    Write-Host "What this script does:"
    Write-Host "- Sets up commit message validation"
    Write-Host "- Adds pre-commit checks for PHP syntax"
    Write-Host "- Validates file permissions"
    Write-Host "- Checks for debugging code"
}

function Install-GitHooks {
    Write-Host "Installing Git Hooks for School ERP System..." -ForegroundColor Green
    
    $hooksDir = ".git/hooks"
    
    if (-not (Test-Path $hooksDir)) {
        Write-Error "Git hooks directory not found. Are you in a git repository?"
        return
    }
    
    # Create commit-msg hook
    $commitMsgHook = @"
#!/bin/sh
# School ERP Commit Message Hook
# Validates commit message format

commit_regex='^(Add|Update|Fix|Remove|Refactor|Style|Docs|Test|Chore): .{10,}'

error_msg="Aborting commit. Your commit message is missing either a type or a description.

Valid commit message format:
Type: Brief description (at least 10 characters)

Valid types: Add, Update, Fix, Remove, Refactor, Style, Docs, Test, Chore

Examples:
Add: new student registration feature
Update: improve attendance calculation performance
Fix: resolve grade calculation bug for partial marks
Remove: deprecated fee structure components
Refactor: optimize database queries in student module
Style: format code according to PSR-12 standards
Docs: update installation instructions in README
Test: add unit tests for grade calculation
Chore: update dependencies and clean up unused files"

if ! grep -qE "`$commit_regex" "`$1"; then
    echo "`$error_msg" >&2
    exit 1
fi
"@
    
    $commitMsgPath = Join-Path $hooksDir "commit-msg"
    $commitMsgHook | Out-File -FilePath $commitMsgPath -Encoding UTF8
    
    # Create pre-commit hook
    $preCommitHook = @"
#!/bin/sh
# School ERP Pre-commit Hook
# Performs checks before allowing commit

echo "Running pre-commit checks..."

# Check for PHP syntax errors
for file in `$(git diff --cached --name-only | grep '\.php$'); do
    if [ -f "`$file" ]; then
        php -l "`$file" > /dev/null 2>&1
        if [ `$? -ne 0 ]; then
            echo "Error: PHP syntax error in `$file"
            exit 1
        fi
    fi
done

# Check for debugging code
debug_patterns=(
    "var_dump"
    "print_r"
    "console\.log"
    "debugger"
    "die("
    "exit("
    "error_log.*debug"
)

for pattern in "`${debug_patterns[@]}"; do
    if git diff --cached | grep -E "`$pattern" > /dev/null; then
        echo "Error: Found debugging code in staged files: `$pattern"
        echo "Please remove debugging code before committing."
        exit 1
    fi
done

# Check for hardcoded passwords or sensitive data
sensitive_patterns=(
    "password.*=.*['\"][^'\"]{3,}"
    "secret.*=.*['\"][^'\"]{3,}"
    "api_key.*=.*['\"][^'\"]{10,}"
    "private_key"
)

for pattern in "`${sensitive_patterns[@]}"; do
    if git diff --cached | grep -iE "`$pattern" > /dev/null; then
        echo "Warning: Potential sensitive data found: `$pattern"
        echo "Please review and ensure no secrets are being committed."
        read -p "Continue with commit? (y/N): " continue_commit
        if [ "`$continue_commit" != "y" ]; then
            exit 1
        fi
    fi
done

echo "Pre-commit checks passed!"
"@
    
    $preCommitPath = Join-Path $hooksDir "pre-commit"
    $preCommitHook | Out-File -FilePath $preCommitPath -Encoding UTF8
    
    # Make hooks executable (if on Unix-like system)
    if ($IsLinux -or $IsMacOS) {
        chmod +x $commitMsgPath
        chmod +x $preCommitPath
    }
    
    Write-Host "Git hooks installed successfully!" -ForegroundColor Green
    Write-Host "- commit-msg: Validates commit message format" -ForegroundColor Gray
    Write-Host "- pre-commit: Checks PHP syntax and debugging code" -ForegroundColor Gray
}

function Uninstall-GitHooks {
    Write-Host "Removing Git Hooks..." -ForegroundColor Yellow
    
    $hooksDir = ".git/hooks"
    $hooks = @("commit-msg", "pre-commit")
    
    foreach ($hook in $hooks) {
        $hookPath = Join-Path $hooksDir $hook
        if (Test-Path $hookPath) {
            Remove-Item $hookPath -Force
            Write-Host "Removed: $hook" -ForegroundColor Gray
        }
    }
    
    Write-Host "Git hooks removed successfully!" -ForegroundColor Green
}

# Main execution
if ($Help) {
    Show-Help
} elseif ($Install) {
    Install-GitHooks
} elseif ($Uninstall) {
    Uninstall-GitHooks
} else {
    Write-Host "School ERP Git Hooks Setup" -ForegroundColor Cyan
    Write-Host "Use -Install to install hooks, -Uninstall to remove them, or -Help for more information."
}
