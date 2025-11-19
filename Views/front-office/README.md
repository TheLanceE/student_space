# EduMind+ Front Office (Student)

Student-facing templates with HTML/CSS/JS (Bootstrap + Chart.js). No backend required; demo data and progress are stored in localStorage.

## What’s included

- Login/Register (localStorage-based)
- Dashboard (suggestions + progress chart + recent results)
- Courses (course cards with quiz links)
- Quiz (timer + instant feedback + score storage)
- Profile (history chart + full results)

## Open locally (Windows)

Option A — open directly:
1. In File Explorer, open `front-office/index.html` in your browser.

Option B — run a simple local server (recommended for routing/Chart.js):

```powershell
# From the project root (Front&Back)
# Using Python 3 (if installed):
python -m http.server 5500
# Then open: http://localhost:5500/front-office/index.html
```

Alternative with Node.js:

```powershell
# If you have Node.js
npx serve -l 5500 .
# Then open: http://localhost:5500/front-office/index.html
```

## Notes
- Demo accounts are created automatically on first login if the username doesn’t exist.
- All data (users, scores, sessions) is stored in `localStorage` under keys like `users`, `currentUser`, and `scores`.
- Suggestion engine is fully rule-based and runs in the browser.
- You can customize courses and quizzes in `assets/js/data.js`.
