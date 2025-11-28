$fixes = @{
    "c:\Users\LanceE\Downloads\Front&Back\Views\teacher-back-office\dashboard.php" = "dashboard.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\teacher-back-office\projects.php" = "projects.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\teacher-back-office\courses.php" = "courses.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\teacher-back-office\events.php" = "events.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\teacher-back-office\students.php" = "students.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\teacher-back-office\quiz-builder.php" = "quiz-builder.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\teacher-back-office\quiz-reports.php" = "quiz-reports.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\teacher-back-office\reports.php" = "reports.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\admin-back-office\dashboard.php" = "dashboard.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\admin-back-office\projects.php" = "projects.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\admin-back-office\users.php" = "users.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\admin-back-office\roles.php" = "roles.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\admin-back-office\courses.php" = "courses.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\admin-back-office\events.php" = "events.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\admin-back-office\quiz-reports.php" = "quiz-reports.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\admin-back-office\logs.php" = "logs.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\admin-back-office\reports.php" = "reports.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\admin-back-office\settings.php" = "settings.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\front-office\dashboard.php" = "dashboard.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\front-office\projects.php" = "projects.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\front-office\courses.php" = "courses.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\front-office\quiz.php" = "quiz.php"
    "c:\Users\LanceE\Downloads\Front&Back\Views\front-office\profile.php" = "profile.php"
}

foreach ($filePath in $fixes.Keys) {
    $activePage = $fixes[$filePath]
    
    if (Test-Path $filePath) {
        $lines = Get-Content $filePath
        $modified = $false
        
        for ($i = 0; $i -lt $lines.Count; $i++) {
            if ($lines[$i] -match 'class="nav-link".*href="([^"]+)"') {
                $href = $matches[1]
                if ($href -eq $activePage) {
                    $lines[$i] = $lines[$i] -replace 'class="nav-link"', 'class="nav-link active" aria-current="page"'
                    $modified = $true
                }
            }
        }
        
        if ($modified) {
            $lines | Set-Content $filePath
            Write-Host "Fixed: $filePath"
        }
    }
}

Write-Host ""
Write-Host "Done!"
