# Git Workflow Quick Reference - School ERP System

## 📋 Branch Structure

```
main    ← Production-ready code (stable releases)
  ↑
dev     ← Development integration branch
  ↑
feature/* ← Feature development branches
hotfix/*  ← Emergency production fixes
```

## 🚀 Quick Start Commands

### PowerShell Workflow Script
```powershell
# Create a new feature
.\git-workflow.ps1 feature user-management

# Check status
.\git-workflow.ps1 status

# Merge feature to dev
.\git-workflow.ps1 merge-feature

# Release to production
.\git-workflow.ps1 release
```

### Manual Git Commands

#### Starting New Work
```bash
# Switch to dev and get latest
git checkout dev
git pull origin dev

# Create feature branch
git checkout -b feature/your-feature-name
```

#### During Development
```bash
# Check status
git status

# Stage changes
git add .

# Commit with proper message
git commit -m "Add: new feature description"

# Push to remote
git push origin feature/your-feature-name
```

#### Finishing Feature
```bash
# Switch to dev
git checkout dev

# Merge feature (no fast-forward for clear history)
git merge feature/your-feature-name --no-ff

# Delete feature branch
git branch -d feature/your-feature-name

# Push to remote
git push origin dev
```

## 📝 Commit Message Format

### Required Format
```
Type: Brief description (minimum 10 characters)

Optional detailed description
- Additional details
- More context
```

### Valid Types
- **Add**: New features or functionality
- **Update**: Improvements to existing features  
- **Fix**: Bug fixes
- **Remove**: Remove features or code
- **Refactor**: Code restructuring without feature changes
- **Style**: Code formatting, no functional changes
- **Docs**: Documentation updates
- **Test**: Adding or updating tests
- **Chore**: Maintenance tasks, dependencies

### Examples
```bash
git commit -m "Add: student attendance tracking module"
git commit -m "Fix: grade calculation error for partial marks"
git commit -m "Update: improve responsive design for mobile devices"
git commit -m "Refactor: optimize database queries in teacher module"
```

## 🌿 Common Workflows

### Feature Development
```bash
# 1. Start feature
git checkout dev
git pull origin dev
git checkout -b feature/new-dashboard

# 2. Develop and commit
git add .
git commit -m "Add: new dashboard layout"
git push origin feature/new-dashboard

# 3. Finish feature
git checkout dev
git merge feature/new-dashboard --no-ff
git branch -d feature/new-dashboard
git push origin dev
```

### Hotfix Workflow
```bash
# 1. Create hotfix from main
git checkout main
git pull origin main
git checkout -b hotfix/security-patch

# 2. Fix and commit
git add .
git commit -m "Fix: critical security vulnerability"

# 3. Merge to main AND dev
git checkout main
git merge hotfix/security-patch --no-ff
git checkout dev
git merge hotfix/security-patch --no-ff

# 4. Cleanup
git branch -d hotfix/security-patch
git push origin main dev
```

### Release Process
```bash
# 1. Ensure dev is ready
git checkout dev
git pull origin dev

# 2. Merge to main
git checkout main
git pull origin main
git merge dev --no-ff

# 3. Tag release
git tag -a v1.0.0 -m "Release version 1.0.0"

# 4. Push everything
git push origin main --tags
```

## 🛠️ Useful Git Aliases

### Setup Aliases
```bash
# Run the alias setup script
.\setup-git-aliases.ps1
```

### Common Aliases
```bash
git st              # status
git co dev          # checkout dev
git br              # list branches
git lg              # pretty log
git new-feature student-portal  # create feature branch
git finish-feature  # merge and delete feature branch
git cleanup         # delete merged branches
```

## 🔍 Troubleshooting

### Merge Conflicts
```bash
# 1. Start merge
git merge feature/branch-name

# 2. Resolve conflicts in editor
# Edit conflicted files, remove conflict markers

# 3. Mark as resolved
git add conflicted-file.php

# 4. Complete merge
git commit
```

### Undo Last Commit (Keep Changes)
```bash
git reset --soft HEAD~1
```

### Undo Changes in Working Directory
```bash
# Single file
git checkout -- filename.php

# All files
git checkout -- .
```

### View Branch History
```bash
git log --oneline --graph --decorate --all
```

## 📊 Branch Status Commands

### Check Branch Status
```bash
# Current branch
git branch --show-current

# All branches
git branch -a

# Remote branches
git branch -r

# Merged branches
git branch --merged
```

### Check Remote Status
```bash
# Fetch updates without merging
git fetch origin

# Compare with remote
git log HEAD..origin/dev --oneline
```

## 🧹 Cleanup Commands

### Delete Merged Feature Branches
```bash
git branch --merged dev | grep feature/ | xargs git branch -d
```

### Prune Remote Branches
```bash
git remote prune origin
```

### Clean Untracked Files
```bash
# See what would be deleted
git clean -n

# Delete untracked files
git clean -f

# Delete untracked files and directories
git clean -fd
```

## 🔒 Pre-commit Checklist

Before committing, ensure:
- [ ] Code follows PSR-12 standards
- [ ] No PHP syntax errors
- [ ] No debugging code (var_dump, console.log, etc.)
- [ ] No hardcoded credentials
- [ ] Commit message follows format
- [ ] Tests pass (if applicable)
- [ ] Documentation updated (if needed)

## 📞 Getting Help

```bash
# Git help
git help <command>

# Show git aliases
git config --get-regexp alias

# Workflow script help
.\git-workflow.ps1 help
```

---

**Remember**: Always work on feature branches, never commit directly to `main` or `dev`!
