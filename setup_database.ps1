Write-Host "========================================"
Write-Host "   Timetable Database Setup Script"
Write-Host "========================================"
Write-Host ""

# Step 1: Check if MySQL is accessible
Write-Host "Step 1: Checking if MySQL is accessible..." -ForegroundColor Yellow
try {
    $null = Get-Command mysql -ErrorAction Stop
    Write-Host "[OK] MySQL found!" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] MySQL is not installed or not in PATH." -ForegroundColor Red
    Write-Host "Please install MySQL or XAMPP first." -ForegroundColor Red
    Write-Host "See setup_mysql.md for detailed instructions." -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""

# Step 2: Create database
Write-Host "Step 2: Creating database..." -ForegroundColor Yellow
Write-Host "Please enter your MySQL root password when prompted:" -ForegroundColor Cyan

$createDbCommand = 'CREATE DATABASE IF NOT EXISTS timetable_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
$result = Start-Process -FilePath "mysql" -ArgumentList "-u", "root", "-p", "-e", "`"$createDbCommand`"" -Wait -PassThru

if ($result.ExitCode -ne 0) {
    Write-Host "[ERROR] Failed to create database. Please check your credentials." -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "[OK] Database created successfully!" -ForegroundColor Green
Write-Host ""

# Step 3: Run Laravel migrations
Write-Host "Step 3: Running Laravel migrations..." -ForegroundColor Yellow
$result = Start-Process -FilePath "php" -ArgumentList "artisan", "migrate" -Wait -PassThru -NoNewWindow

if ($result.ExitCode -ne 0) {
    Write-Host "[ERROR] Migration failed. Please check your .env configuration." -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "[OK] Migrations completed!" -ForegroundColor Green
Write-Host ""

# Step 4: Seed database
Write-Host "Step 4: Seeding database with sample data..." -ForegroundColor Yellow
Start-Process -FilePath "php" -ArgumentList "artisan", "db:seed", "--class=AdminUserSeeder" -Wait -NoNewWindow
Start-Process -FilePath "php" -ArgumentList "artisan", "db:seed", "--class=SampleDataSeeder" -Wait -NoNewWindow

Write-Host ""
Write-Host "========================================"
Write-Host "   Setup Complete!"
Write-Host "========================================"
Write-Host ""
Write-Host "Your timetable database is now ready!" -ForegroundColor Green
Write-Host ""
Write-Host "Default admin credentials:" -ForegroundColor Cyan
Write-Host "Email: admin@example.com" -ForegroundColor White
Write-Host "Password: password" -ForegroundColor White
Write-Host ""
Write-Host "Run 'php artisan serve' to start the application." -ForegroundColor Yellow
Write-Host ""
Read-Host "Press Enter to exit"
