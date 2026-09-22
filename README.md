# StudySpot

Find quiet places to study across Sri Lanka — libraries, cafés, co-working spaces and university areas.
Web Architecture group project. Built with plain **HTML, CSS, PHP (PDO) and MySQL** — no frameworks.

---

## 1. Run it on your machine

You need **XAMPP** (or WAMP / MAMP) — it gives you Apache, PHP and MySQL together.

1. Copy the whole `studyspot` folder into `C:\xampp\htdocs\`
   (Mac: `/Applications/XAMPP/htdocs/`).
2. Open the XAMPP Control Panel and start **Apache** and **MySQL**.
3. Go to <http://localhost/phpmyadmin> → **Import** → choose `database/studyspot.sql` → **Go**.
   That creates the `studyspot` database with sample places, reviews and bookings.
4. Open <http://localhost/studyspot/>.

Demo login: **sahan@example.com** / **123456**

If your MySQL uses a password (MAMP uses `root`), change it in `config/db.php`.

---

## 2. What each file does

```
studyspot/
├─ index.php               Home / landing page
├─ explore.php             Search + filters + pagination
├─ place.php               One study space: photos, facts, facilities, reviews
├─ map.php                 Map view + nearby list
├─ login.php               Login
├─ register.php            Create account
├─ logout.php              Ends the session
├─ profile.php             Dashboard: overview, favourites, reviews, recently viewed, settings
├─ favorites.php           My Favourites
├─ my-bookings.php         Upcoming / Completed / Cancelled tabs, cancel a booking
├─ booking.php             Step 1: date, time, people
├─ payment.php             Step 2: summary + card details
├─ booking-confirmed.php   Step 3: confirmation
├─ review.php              Write a review
├─ help.php                Help & FAQ
├─ about.php               About Us
├─ actions/
│  └─ toggle-favorite.php  Add / remove a favourite (POST only)
├─ config/
│  └─ db.php               Database connection (PDO)
├─ includes/
│  ├─ functions.php        Shared helpers: e(), require_login(), place_rating(), stars()...
│  ├─ header.php           Nav bar + <head> for every page
│  └─ footer.php           Footer + scripts
├─ assets/
│  ├─ css/style.css        All styling (colours match the Figma file)
│  ├─ js/main.js           Star rating, people counter, geolocation, filters
│  └─ img/                 Cover photos (replace the placeholders with real ones)
└─ database/
   └─ studyspot.sql        Tables + sample data
```

### Database tables

`users`, `places`, `place_images`, `reviews`, `favorites`, `bookings`, `recently_viewed`.

---

## 3. Images and logo

Everything in `assets/img/` comes straight out of the Figma file, so the site looks exactly
like the design:

- `logo.png` — the StudySpot mark (also used as the browser favicon)
- `hero.png` / `hero.jpg` — the illustration on the landing page
- `review-guide.png` — the illustration in the Review Guidelines panel
- Place photos: `national-library*.jpg`, `cafe-kumbuk*.jpg`, `hub-lanka*.jpg`, `uoc-library*.jpg`,
  `library-cafe*.jpg`, `mind-space*.jpg`, `book-haven*.jpg`, `green-space*.jpg`

The cover photo of each place is the `cover_image` column in the `places` table; the extra
thumbnails under the main photo come from the `place_images` table. To swap a photo, either drop
a new file in with the same name, or change the file name in the database — no code change needed.

### Using Google Maps instead of OpenStreetMap

`map.php` uses a free OpenStreetMap iframe so it works with no API key. If your group has a
Google Maps key, replace the `<iframe src="...">` in `map.php` with:

```
https://www.google.com/maps/embed/v1/view?key=YOUR_KEY&center=6.9271,79.8612&zoom=12
```

---

## 4. Splitting the work between 5 members

Each member owns whole files, so two people rarely edit the same file. That is what keeps
merge conflicts away.

| Member | Owns these files | Job |
|---|---|---|
| **1 — Core & Home** | `includes/header.php`, `includes/footer.php`, `includes/functions.php`, `assets/css/style.css`, `index.php`, `about.php` | Layout, nav, shared styling and the landing page. Everyone depends on this, so finish it first. |
| **2 — Search & Map** | `explore.php`, `map.php` | Filters (type, Wi-Fi, noise, cost, distance), sorting, pagination, map + nearby list. |
| **3 — Accounts** | `login.php`, `register.php`, `logout.php`, `profile.php` | Validation, `password_hash` / `password_verify`, sessions, profile dashboard and settings. |
| **4 — Booking** | `booking.php`, `payment.php`, `booking-confirmed.php`, `my-bookings.php` | The whole booking flow, price calculation, cancelling. |
| **5 — Content & Database** | `database/studyspot.sql`, `place.php`, `review.php`, `favorites.php`, `actions/toggle-favorite.php`, `help.php` | Schema and sample data, place details, reviews, favourites, FAQ. |

Shared files (`functions.php`, `style.css`) belong to Member 1 — if you need a new helper or a new
CSS class, ask them to add it rather than editing it yourself at the same time.

---

## 5. Publishing to GitHub from VS Code

**One person creates the repo (do this once):**

1. In VS Code: **File → Open Folder** → pick the `studyspot` folder.
2. Open the **Source Control** panel (the branch icon on the left, or `Ctrl+Shift+G`).
3. Click **Initialize Repository**.
4. Type a message like `Initial commit` and press **Commit**.
5. Click **Publish Branch** → choose **Public** (or Private) → VS Code creates the GitHub repo.
6. On github.com open the repo → **Settings → Collaborators** → invite the other 4 members.

**The other 4 members:**

1. VS Code → `Ctrl+Shift+P` → **Git: Clone** → paste the repo URL → pick a folder.
2. Put that folder inside `htdocs` so XAMPP can serve it.

**Everyday workflow — use a branch each, never push straight to `main`:**

```bash
git checkout main
git pull                      # get everyone else's latest work
git checkout -b explore-page  # your own branch
# ...edit your files...
git add .
git commit -m "Add cost filter to explore page"
git push -u origin explore-page
```

Then on github.com press **Compare & pull request**, ask a teammate to review, and **Merge**.
After merging, everyone runs `git pull` on `main`.

**Rules that prevent 90% of group project problems:**

- Pull before you start working, every single time.
- One feature per branch, small commits with a real message.
- Never commit `config/db.php` with a real password (`.gitignore` already covers `db.local.php`).
- If you hit a conflict, do not delete the other person's code — open the file, keep both parts,
  and ask them before you resolve it.

---

## 6. Things you could add if you have time

- Admin panel to add and edit study spaces
- Email confirmation after a booking (PHPMailer)
- Stopping double bookings for the same place, date and time slot
- Real photo uploads instead of fixed file names
- "Forgot password" with a reset token

---

## 7. Notes for the report

- Every database query uses **prepared statements**, so SQL injection is blocked.
- Every value printed to the page goes through `e()` (`htmlspecialchars`), so XSS is blocked.
- Passwords are stored as bcrypt hashes with `password_hash()` — never in plain text.
- `payment.php` is a demo checkout: no card is charged and no card data is saved, only a
  generated reference like `SS-C873AB`.
- The layout is responsive — one column on phones, with a hamburger menu in the nav bar.
