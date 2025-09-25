# MySQL Setup Guide for Timetable Application

## Step 1: Install MySQL Server

### For Windows:
1. Download MySQL from: https://dev.mysql.com/downloads/mysql/
2. Download the MySQL Installer for Windows
3. Run the installer and choose "Developer Default" setup
4. Set a root password during installation (remember this password!)
5. Complete the installation

### Alternative: Using XAMPP (Easier for beginners)
1. Download XAMPP from: https://www.apachefriends.org/
2. Install XAMPP (includes MySQL, PHP, and Apache)
3. Start XAMPP Control Panel
4. Start MySQL service

## Step 2: Create Database

### Option A: Using MySQL Command Line
```bash
mysql -u root -p
CREATE DATABASE timetable_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit
```

### Option B: Using phpMyAdmin (if using XAMPP)
1. Open http://localhost/phpmyadmin in your browser
2. Click "New" to create a new database
3. Enter database name: `timetable_db`
4. Choose collation: `utf8mb4_unicode_ci`
5. Click "Create"

## Step 3: Update Environment Configuration

Update your `.env` file with these database settings:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=timetable_db
DB_USERNAME=root
DB_PASSWORD=your_mysql_password_here
```

## Step 4: Run Database Setup Commands

Open your terminal in the project directory and run:

```bash
# Install dependencies (if not already done)
composer install

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=SampleDataSeeder

# Or run all seeders at once
php artisan db:seed
```

## Step 5: Test the Application

```bash
# Start the Laravel development server
php artisan serve

# Visit http://localhost:8000 to see the timetable
# Visit http://localhost:8000/admin to access admin panel
```

## Default Admin Credentials

After running the seeders, you can log in to the admin panel with:
- **Email**: admin@example.com
- **Password**: password

## What Data is Created

The seeder will create:
- **30 days of prayer times** with realistic variations
- **7 authentic hadeeths** in Arabic and English
- **6 sample announcements** for different occasions
- **Default masjid settings**
- **Admin user account**

## Troubleshooting

### Common Issues:

1. **"Access denied for user 'root'"**
   - Check your MySQL password in `.env` file
   - Make sure MySQL service is running

2. **"Database does not exist"**
   - Create the database manually using phpMyAdmin or MySQL command line

3. **"Class not found" errors**
   - Run: `composer dump-autoload`

4. **Migration errors**
   - Drop all tables and run: `php artisan migrate:fresh --seed`

### Need Help?
- Check Laravel logs: `storage/logs/laravel.log`
- Verify MySQL is running: Check XAMPP Control Panel or Windows Services
- Test database connection: `php artisan tinker` then `DB::connection()->getPdo()`
