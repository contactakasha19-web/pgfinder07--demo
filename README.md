# StayNest: Student PG Accommodation Website

A responsive PG (Paying Guest) finder for students. Built with HTML, CSS, Bootstrap 5, JavaScript (AJAX), PHP (PDO), MySQL and React.

## 1. Project overview
Students can browse PG listings, filter by city / budget / gender, open a detail page with gallery and amenities, sign up, and shortlist properties. Filtering and shortlisting happen without page reloads.

## 2. Folder structure
```
pg-finder/
├── index.php            Listing page (AJAX filters)
├── property.php         Detail page (gallery, amenities, rating, shortlist button)
├── shortlist.php        Shortlist page (React component)
├── signup.php / login.php / logout.php
├── api/
│   ├── properties.php       GET  filtered listings as JSON
│   ├── toggle_interest.php  POST mark / unmark interest
│   └── shortlist.php        GET  current user's shortlist
├── config/db.php        Database credentials + PDO connection
├── includes/            init.php (session, helpers), header.php, footer.php
├── assets/css/style.css
├── assets/js/app.js         AJAX filters, toggle, toasts
├── assets/js/shortlist.js   React shortlist component
└── database/schema.sql      Tables + sample data
```

## 3. Database design
Five required tables plus `property_images` for the gallery.

| Table | Columns |
|---|---|
| users | id, name, email (unique), password (bcrypt hash), phone |
| properties | id, name, city, area, price, gender, rating, description, image |
| amenities | id, name |
| property_amenities | property_id, amenity_id (composite PK) |
| interested_users | user_id, property_id (composite PK) |
| property_images | id, property_id, image |

Normalisation: amenities live in their own table and are linked many-to-many through `property_amenities`; shortlists are a many-to-many between users and properties. Foreign keys use `ON DELETE CASCADE`. Indexes on city, price and gender speed up the filters.

## 4. Approach
1. **Schema first**: designed tables and loaded sample data.
2. **PHP backend**: PDO prepared statements everywhere (prevents SQL injection); `password_hash` / `password_verify` for passwords; session ID regenerated on login; output escaped with `htmlspecialchars`.
3. **Pages**: listing, detail, signup and login rendered with Bootstrap's grid, so layouts reflow on phones, tablets and desktops.
4. **AJAX**: the listing page fetches JSON from `api/properties.php` whenever a filter changes (200 ms debounce). A spinner shows during loading, stale responses are ignored, and empty or error states have clear messages. The heart button calls `api/toggle_interest.php` and updates instantly.
5. **Security**: CSRF token (in a meta tag, sent as `X-CSRF-Token`) required on the toggle endpoint; server returns 401 when not logged in and the UI sends the user to login; redirect targets after login are validated.
6. **React**: the shortlist page is a React component (`Shortlist` + `PropertyCard`) using `useState` and `useEffect`. It loads via `fetch`, removes items with instant UI updates, and handles loading, empty and error states. It uses the React UMD build, so no build step is required.
7. **UI improvements** (after review): large tap targets for mobile, visible keyboard focus, `aria-live` result counts and `aria-pressed` on toggles, lazy-loaded images, reduced-motion support, whole-card click targets.

## 5. Run locally
1. Install XAMPP / MAMP (PHP 8+, MySQL).
2. Copy the folder into `htdocs/pg-finder`.
3. Open phpMyAdmin → Import → choose `database/schema.sql`.
4. Edit `config/db.php` if your MySQL user or password differs.
5. Visit `http://localhost/pg-finder/`.

## 6. Deploy (free option: InfinityFree)
1. Create an account at infinityfree.com and create a hosting account (you get a subdomain).
2. In the control panel, open **MySQL Databases**, create a database, and note host, name, user and password (the host is not `localhost`).
3. Open **phpMyAdmin**, select the database, and import `database/schema.sql`. Delete the first two lines (`CREATE DATABASE` and `USE`) before importing, since the host creates the database for you.
4. Put your credentials into `config/db.php`.
5. Upload all files into the `htdocs` folder using the online File Manager or FileZilla (FTP).
6. Open your subdomain URL and test: browse, filter, sign up, shortlist.
Other options: 000webhost-style hosts, Hostinger, or any cPanel host.

## 7. GitHub
```
git init
git add .
git commit -m "StayNest PG finder"
git branch -M main
git remote add origin https://github.com/<you>/pg-finder.git
git push -u origin main
```
Do not commit real database passwords: use placeholders in `config/db.php` or add it to `.gitignore` and commit a `db.sample.php`.

## 8. Screenshot checklist
1. Listing page (desktop and mobile width).
2. Detail page showing gallery, amenities, rating.
3. AJAX interaction: filters applied with the spinner or updated count, and the heart toggling with a toast.

## 9. Possible improvements
Owner dashboard to add listings, pagination, map view, reviews, image upload, email verification.
