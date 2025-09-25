@echo off
echo ========================================
echo   Timetable Database Setup Script
echo ========================================
echo.

echo Step 1: Checking if MySQL is accessible...
mysql --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] MySQL is not installed or not in PATH.
    echo Please install MySQL or XAMPP first.
    echo See setup_mysql.md for detailed instructions.
    pause
    exit /b 1
)

echo [OK] MySQL found!
echo.

echo Step 2: Creating database...
echo Please enter your MySQL root password when prompted:
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS timetable_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if %errorlevel% neq 0 (
    echo [ERROR] Failed to create database. Please check your credentials.
    pause
    exit /b 1
)

echo [OK] Database created successfully!
echo.

echo Step 3: Running Laravel migrations...
php artisan migrate
if %errorlevel% neq 0 (
    echo [ERROR] Migration failed. Please check your .env configuration.
    pause
    exit /b 1
)

echo [OK] Migrations completed!
echo.

echo Step 4: Seeding database with sample data...
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=SampleDataSeeder

echo.
echo ========================================
echo   Setup Complete!
echo ========================================
echo.
echo Your timetable database is now ready!
echo.
echo Default admin credentials:
echo Email: admin@example.com
echo Password: password
echo.
echo Run 'php artisan serve' to start the application.
echo.
pause
