# Deployment Guide

1. **Upload Project Files**
   Upload the entire project directory (excluding files mentioned in `.gitignore` such as `.env` and `vendor/`) to your Hostinger shared hosting server.

2. **Run Composer (If applicable)**
   If you have SSH access, run `composer install --no-dev --optimize-autoloader`. If you don't have SSH access, you can safely upload the `vendor/` folder from your local machine to the server.

3. **Create `.env` File**
   On the server, copy `.env.example` to `.env`. Do NOT commit this file to version control.

4. **Configure Environment Variables**
   Set the following variables in your `.env` for production:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://importwale.com` (No trailing slash)
   - `APP_BASE_PATH=` (Leave empty for root domain, or `/folder` if in a subfolder)
   - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (Your live database credentials)
   - `CACHE_DRIVER=file`
   - `SESSION_DRIVER=file`
   - Add your R2 and mail credentials.

5. **Set Folder Permissions**
   - Directories should generally be `755`.
   - Files should be `644`.
   - `storage/` and all its subdirectories (including `logs/` and `cache/`) must be `775`.
   - `public/uploads/` must be `775`.

6. **Test the Site**
   Visit `https://importwale.com` to ensure the site loads correctly. Test the health check script if necessary.
