<#
push_to_github.ps1
Helper script to install Git/GitHub CLI (if available via winget), initialize a git repo,
create a `projects` branch, commit current changes, and push to remote.

SECURITY: This script does NOT accept or use GitHub tokens. Authenticate locally using
`gh auth login` or Windows credential manager before running the push portion.
#>

param(
    [string]$RepoPath = (Get-Location).Path,
    # Default remote URL updated to the repository you provided. You can override with -RemoteUrl if needed.
    [string]$RemoteUrl = 'https://github.com/TheLanceE/student_space.git'
)

function Ensure-Program($exe, $wingetId) {
    $cmd = Get-Command $exe -ErrorAction SilentlyContinue
    if ($cmd) { return $true }

    Write-Host "$exe not found. Attempting to install via winget..." -ForegroundColor Yellow
    if (Get-Command winget -ErrorAction SilentlyContinue) {
        try {
            winget install --id $wingetId -e --accept-package-agreements --accept-source-agreements
            return (Get-Command $exe -ErrorAction SilentlyContinue) -ne $null
        } catch {
            Write-Host "winget install failed for $wingetId. Please install $exe manually." -ForegroundColor Red
            return $false
        }
    } else {
        Write-Host "winget not available. Please install $exe manually from its official site." -ForegroundColor Red
        return $false
    }
}

Write-Host "Repository path: $RepoPath" -ForegroundColor Cyan
Set-Location -Path $RepoPath

# Ensure Git
$hasGit = Ensure-Program -exe 'git' -wingetId 'Git.Git'
if (-not $hasGit) { Write-Host "Git is required to continue. Aborting." -ForegroundColor Red; exit 1 }

# Ensure gh (GitHub CLI)
$hasGh = Ensure-Program -exe 'gh' -wingetId 'GitHub.cli'
if (-not $hasGh) {
    Write-Host "Warning: gh (GitHub CLI) not installed. You can still push with git, but you must configure credentials manually." -ForegroundColor Yellow
}

# Confirm authentication method
if ($hasGh) {
    $auth = & gh auth status 2>$null
    if ($LASTEXITCODE -ne 0) {
        Write-Host "You are not authenticated with GitHub CLI. Run `gh auth login` now to authenticate (recommended)." -ForegroundColor Yellow
        Write-Host "If you prefer, authenticate with Git credential manager or set up SSH keys." -ForegroundColor Yellow
        Read-Host -Prompt "Press Enter after you have authenticated with GitHub (or Ctrl+C to cancel)"
    }
}

# Initialize git if needed
if (-not (Test-Path -Path .git)) {
    git init
    Write-Host "Initialized new git repository." -ForegroundColor Green
}

# Add remote if provided and not already set
if ($RemoteUrl) {
    $currentRemote = git remote get-url origin 2>$null
    if ($LASTEXITCODE -ne 0) {
        git remote add origin $RemoteUrl
        Write-Host "Added remote origin $RemoteUrl" -ForegroundColor Green
    } else {
        Write-Host "Remote origin already exists: $currentRemote" -ForegroundColor Cyan
    }
} else {
    Write-Host "No remote URL provided. If you want to push to GitHub, provide the repo URL when running the script via -RemoteUrl 'https://github.com/owner/repo.git'" -ForegroundColor Yellow
}

# Create and checkout branch
$branchName = 'projects'
$existing = git rev-parse --verify $branchName 2>$null
if ($LASTEXITCODE -eq 0) {
    git checkout $branchName
    Write-Host "Checked out existing branch '$branchName'." -ForegroundColor Green
} else {
    git checkout -b $branchName
    Write-Host "Created and checked out branch '$branchName'." -ForegroundColor Green
}

# Stage and commit
git add -A
$hasStaged = (git diff --cached --name-only) -ne $null
if ($hasStaged) {
    $msg = "Migrate localStorage to DB: add schema and DB README"
    git commit -m "$msg" | Out-Null
    Write-Host "Committed changes: $msg" -ForegroundColor Green
} else {
    Write-Host "No changes to commit." -ForegroundColor Yellow
}

# Push
if ($RemoteUrl -or (git remote get-url origin 2>$null)) {
    Write-Host "About to push branch '$branchName' to remote 'origin'." -ForegroundColor Cyan
    Write-Host "If you haven't authenticated, please authenticate now (e.g. `gh auth login` or Git credential manager)." -ForegroundColor Yellow
    Read-Host -Prompt "Press Enter to continue with push (or Ctrl+C to cancel)"

    try {
        git push -u origin $branchName
        Write-Host "Pushed branch '$branchName' to origin." -ForegroundColor Green
    } catch {
        Write-Host "Push failed. Ensure you have permission and are authenticated. See README_DB.md for guidance." -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "No remote configured. Skipping push." -ForegroundColor Yellow
}

Write-Host "Script finished." -ForegroundColor Cyan
