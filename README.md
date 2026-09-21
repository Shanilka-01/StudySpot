# StudySpot

Find quiet places to study across Sri Lanka: libraries, cafés, co-working spaces and university areas.
Web Architecture group project. Built with plain **HTML, CSS, PHP (PDO) and MySQL**, no frameworks.

---

## 1. Run it on your machine

You need **XAMPP** (or WAMP / MAMP). It gives you Apache, PHP and MySQL together.

1. Put the project folder inside `C:\xampp\htdocs\` (Mac: `/Applications/XAMPP/htdocs/`).
   The folder can have any name; the site works out its own address.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open <http://localhost/phpmyadmin> → **Import** → choose `database/studyspot.sql` → **Go**.
   That creates the `studyspot` database with sample places, reviews and bookings.
4. Open `http://localhost/<your-folder-name>/`, for example <http://localhost/StudytSpot/>.

Demo login: **sahan@example.com** / **123456**

If your MySQL has a password (MAMP uses `root`), change it in `config/db.php`.

---

## 2. Folder structure

```
StudySpot/
├─ index.php                  Home / landing page
├─ pages/                     Every other page of the site
│  ├─ about.php               About Us
│  ├─ explore.php             Search + filters + pagination
│  ├─ place.php               One study space: photos, facts, facilities, reviews
│  ├─ map.php                 Map view + nearby list
│  ├─ login.php               Login
│  ├─ register.php            Create account
│  ├─ logout.php              Ends the session
│  ├─ profile.php             Dashboard: overview, favourites, reviews, recently viewed, settings
│  ├─ favorites.php           My Favourites
│  ├─ toggle-favorite.php     Add / remove a favourite (POST only, prints nothing)
│  ├─ my-bookings.php         Upcoming / Completed / Cancelled tabs, cancel a booking
│  ├─ booking.php             Booking step 1: date, time, people
│  ├─ payment.php             Booking step 2: summary + card details
│  ├─ booking-confirmed.php   Booking step 3: confirmation
│  ├─ review.php              Write a review
│  └─ help.php                Help & FAQ
├─ admin/                     Reserved for the admin panel (empty for now)
├─ includes/
│  ├─ functions.php           Shared helpers: url(), asset(), redirect(), e(), require_login() ...
│  ├─ header.php              <head> + nav bar for every page
│  └─ footer.php              Footer + scripts
├─ config/
│  └─ db.php                  Database connection (PDO)
├─ assets/
│  ├─ css/style.css           All styling (colours match the Figma file)
│  ├─ js/main.js              Star rating, people counter, geolocation, filters
│  └─ img/                    Logo, illustrations, place photos
├─ database/
│  └─ studyspot.sql           Tables + sample data
├─ .gitignore
└─ README.md
```

### How links work (read this before adding a page)

Pages live in `pages/`, so never write a plain `href="explore.php"`. Use the helpers from
`includes/functions.php`, which work in any folder name:

```php
<a href="<?= url('pages/explore.php') ?>">Explore</a>
<img src="<?= asset('img/logo.png') ?>" alt="">
<?php redirect('pages/login.php'); ?>
```

A new page inside `pages/` starts like this:

```php
<?php
require_once __DIR__ . '/../includes/functions.php';
$page_title = 'My Page';
require __DIR__ . '/../includes/header.php';
?>
... your HTML ...
<?php require __DIR__ . '/../includes/footer.php'; ?>
```

### Database tables

`users`, `places`, `place_images`, `reviews`, `favorites`, `bookings`, `recently_viewed`.

---

## 3. Images and logo

Everything in `assets/img/` comes straight out of the Figma file, so the site looks like the design:

- `logo.png`: the StudySpot mark (also the browser favicon)
- `hero.jpg`: the landing page illustration
- `review-guide.png`: the illustration in the Review Guidelines panel
- Place photos: `national-library*.jpg`, `cafe-kumbuk*.jpg`, `hub-lanka*.jpg`, `uoc-library*.jpg`,
  `library-cafe*.jpg`, `mind-space*.jpg`, `book-haven*.jpg`, `green-space*.jpg`

A place's cover photo is the `cover_image` column in the `places` table; the thumbnails under the
main photo come from the `place_images` table. To swap a photo, drop a new file in with the same
name, or change the file name in the database.

### Using Google Maps instead of OpenStreetMap

`pages/map.php` uses a free OpenStreetMap iframe so it works with no API key. If your group has a
Google Maps key, replace the `<iframe src="...">` with:

```
https://www.google.com/maps/embed/v1/view?key=YOUR_KEY&center=6.9271,79.8612&zoom=12
```

---

## 4. Roles and who owns which files

The team has five roles, one per member, and each role has its own Git branch. Each role owns
whole files, so two people rarely edit the same file. That is what keeps merge conflicts away.

| Branch | Owns these files | Job |
|---|---|---|
| `frontend` | `assets/*`, `includes/header.php`, `includes/footer.php`, `index.php`, `pages/about.php`, `pages/help.php`, `pages/map.php`, `pages/place.php` | Make every page match the Figma design (fonts, spacing, colours), responsive layout, images. |
| `backend` | `config/*`, `includes/functions.php`, `pages/explore.php`, `login.php`, `register.php`, `logout.php`, `profile.php`, `booking.php`, `payment.php`, `booking-confirmed.php`, `my-bookings.php`, `review.php`, `favorites.php`, `toggle-favorite.php` | PHP logic: validation, sessions, search, booking rules (for example blocking double bookings). |
| `database` | `database/studyspot.sql` | Schema, indexes, sample data, a `role` column on `users` for the admin panel. |
| `admin` | `admin/*` (empty at the start) | Build the admin panel: admin login, add / edit / delete places, view bookings. |
| `testing` | a new `tests/` folder | Write test cases (login, search, booking ...), report bugs as GitHub **Issues**. |

Rules for shared code:

- CSS belongs to `frontend`. If you are `backend` and a page needs a new CSS class, ask `frontend`.
- If `frontend` must change the HTML of a `backend` page, tell the backend member first and keep the change small.
- `admin` needs a `role` column from `database`; agree on the column name before you both start.

---

## 5. Working with GitHub

`main` is the finished, working site. Every member works on their own role branch and merges into
`main` with a Pull Request. The five role branches are `frontend`, `backend`, `database`, `admin`, `testing`.

**First time (everyone except the person who created the repo):**

```bash
git config --global user.name  "Your Name"
git config --global user.email "the email on your GitHub account"
git clone <repo-url>
cd <repo-folder>
git switch frontend          # your own branch name
```

Use `git switch`, not `git checkout`. The folders `admin/` and `database/` have the same names as
branches, so `git checkout admin` fails with "could be both a local file and a tracking branch".

**Every day:**

```bash
git switch <your-branch>
git pull origin main         # get what the others already merged
# ...edit your files...
git status                   # only YOUR files should be listed
git add .
git commit -m "Update home page to match Figma"
git push
```

**Merging:** on github.com press **Compare & pull request** (base `main`, compare your branch),
ask a teammate to review it, then **Merge**. After that everyone runs `git switch <your-branch>`
and `git pull origin main`.

**Rules that prevent most group project problems:**

- Never use `git push --force`. If Git refuses a push, copy the message and ask the team.
- Never push straight to `main`; always use a Pull Request.
- Never commit `config/db.php` with a real password.
- If you hit a merge conflict, keep both parts and ask the other person before you resolve it.
- Everyone commits their own work from their own account, so the GitHub history shows who did what.

---

## 6. Things you could add if you have time

- Admin panel in `admin/` (add and edit study spaces)
- Email confirmation after a booking (PHPMailer)
- Stopping double bookings for the same place, date and time slot
- Real photo uploads instead of fixed file names
- "Forgot password" with a reset token

---

## 7. Notes for the report

- Every database query uses **prepared statements**, so SQL injection is blocked.
- Every value printed to the page goes through `e()` (`htmlspecialchars`), so XSS is blocked.
- Passwords are stored as bcrypt hashes with `password_hash()`, never in plain text.
- After login the "go back to where you were" address is checked (`safe_redirect_target()`), so a
  crafted link cannot send someone to another website.
- `pages/payment.php` is a demo checkout: no card is charged and no card data is saved, only a
  generated reference like `SS-C873AB`.
- The layout is responsive: one column on phones, with a hamburger menu in the nav bar.
