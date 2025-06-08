# Git Aliases Setup for School ERP System
# Run this script to set up useful git aliases

Write-Host "Setting up Git Aliases for School ERP System..." -ForegroundColor Green

# Basic workflow aliases
git config alias.co checkout
git config alias.br branch
git config alias.st status
git config alias.cm commit

# Branch management aliases
git config alias.dev 'checkout dev'
git config alias.main 'checkout main'
git config alias.new-feature '!f() { git checkout dev && git pull origin dev && git checkout -b feature/$1; }; f'
git config alias.new-hotfix '!f() { git checkout main && git pull origin main && git checkout -b hotfix/$1; }; f'

# Merge aliases
git config alias.merge-to-dev '!f() { BRANCH=$(git branch --show-current) && git checkout dev && git pull origin dev && git merge $BRANCH --no-ff; }; f'
git config alias.release '!f() { git checkout main && git pull origin main && git merge dev --no-ff; }; f'

# Log and status aliases
git config alias.lg "log --color --graph --pretty=format:'%Cred%h%Creset -%C(yellow)%d%Creset %s %Cgreen(%cr) %C(bold blue)<%an>%Creset' --abbrev-commit"
git config alias.ls "log --pretty=format:'%C(yellow)%h %Cred%ad %Cblue%an%Creset %s' --date=short"
git config alias.branches "branch -a"
git config alias.remotes "remote -v"

# Cleanup aliases
git config alias.cleanup-merged "!git branch --merged | grep 'feature/' | xargs -r git branch -d"
git config alias.cleanup-remote "remote prune origin"

# Quick commit aliases
git config alias.add-all "add ."
git config alias.amend "commit --amend --no-edit"
git config alias.uncommit "reset --soft HEAD~1"

# Show aliases
git config alias.aliases "config --get-regexp alias"

Write-Host ""
Write-Host "Git aliases set up successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "Available aliases:" -ForegroundColor Yellow
Write-Host "  Basic Commands:"
Write-Host "    git co <branch>     # checkout"
Write-Host "    git br              # branch list"
Write-Host "    git st              # status"
Write-Host "    git cm '<message>'  # commit"
Write-Host ""
Write-Host "  Workflow Commands:"
Write-Host "    git dev             # switch to dev branch"
Write-Host "    git main            # switch to main branch"
Write-Host "    git new-feature <name>  # create new feature branch"
Write-Host "    git new-hotfix <name>   # create new hotfix branch"
Write-Host "    git merge-to-dev    # merge current branch to dev"
Write-Host "    git release         # merge dev to main"
Write-Host ""
Write-Host "  Information Commands:"
Write-Host "    git lg              # pretty log with graph"
Write-Host "    git ls              # simple log list"
Write-Host "    git branches        # all branches"
Write-Host "    git remotes         # remote repositories"
Write-Host ""
Write-Host "  Cleanup Commands:"
Write-Host "    git cleanup-merged  # delete merged feature branches"
Write-Host "    git cleanup-remote  # prune remote branches"
Write-Host ""
Write-Host "  Quick Commands:"
Write-Host "    git add-all         # add all files"
Write-Host "    git amend           # amend last commit"
Write-Host "    git uncommit        # undo last commit (keep changes)"
Write-Host ""
Write-Host "  Show all aliases: git aliases"
