# Account Deletion Test Verification Script
Write-Host "`n=== VERIFYING ACCOUNT DELETION ===" -ForegroundColor Cyan

# Check if alice's account has deleted_at timestamp
Write-Host "`n1. Checking alice's account status..." -ForegroundColor Yellow
cd "C:\xampp\mysql\bin"
$result = .\mysql.exe -u root -e "USE edumind; SELECT id, username, email, deleted_at FROM students WHERE username='alice';" -s

if ($result -match "NULL") {
    Write-Host "   ❌ FAILED: Account NOT deleted (deleted_at is NULL)" -ForegroundColor Red
} elseif ($result -match "\d{4}-\d{2}-\d{2}") {
    Write-Host "   ✅ PASSED: Account soft deleted!" -ForegroundColor Green
    Write-Host "`n   Account Details:" -ForegroundColor Cyan
    .\mysql.exe -u root -e "USE edumind; SELECT username, email, deleted_at FROM students WHERE username='alice';" -t
} else {
    Write-Host "   ⚠️  Account may have been hard deleted or not found" -ForegroundColor Yellow
}

# Try to login with deleted account
Write-Host "`n2. Testing login with deleted account..." -ForegroundColor Yellow
try {
    $body = @{
        username = "alice"
        password = "password123"
    } | ConvertTo-Json

    $response = Invoke-WebRequest -Uri "http://localhost/edumind/Controllers/AuthController.php?action=login&role=student" `
        -Method POST -Body $body -ContentType "application/json" -UseBasicParsing
    
    $json = $response.Content | ConvertFrom-Json
    
    if ($json.success -eq $false) {
        Write-Host "   ✅ PASSED: Login correctly rejected for deleted account" -ForegroundColor Green
        Write-Host "   Message: $($json.message)" -ForegroundColor Gray
    } else {
        Write-Host "   ❌ FAILED: Deleted account can still login!" -ForegroundColor Red
    }
} catch {
    Write-Host "   ℹ️  Could not test login (server may have different response)" -ForegroundColor Yellow
}

# Show all deleted accounts
Write-Host "`n3. All deleted accounts in database:" -ForegroundColor Yellow
.\mysql.exe -u root -e "USE edumind; SELECT 'Students' as type, id, username, deleted_at FROM students WHERE deleted_at IS NOT NULL UNION SELECT 'Teachers', id, username, deleted_at FROM teachers WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC;" -t

Write-Host "`n=== TEST VERIFICATION COMPLETE ===`n" -ForegroundColor Cyan
