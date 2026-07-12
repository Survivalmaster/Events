<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Events Portal - Create Environmental Event</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link rel="icon" type="image/png" href="assets/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />

  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="css/environmental-events.css" />
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="sidebar__logo">
      <div class="logo-circle">
        <img src="assets/logo.png" alt="Events Logo" class="logo-img">
      </div>
      <div class="logo-text">
        <div class="logo-title">Events Team</div>
        <div class="logo-subtitle">The Events Team Portal</div>
      </div>
    </div>

    <nav class="sidebar__nav">
      <div class="sidebar__section">
        <div class="sidebar__section-title">Navigation</div>
        <a href="index.html" class="sidebar__item">
          <i class="fa-solid fa-gauge-high"></i>
          <span>Dashboard</span>
        </a>
      </div>

      <div class="sidebar__section">
        <div class="sidebar__section-title">Sections</div>
        <a href="environmental-events-wizard.html" class="sidebar__item sidebar__item--active">
          <i class="fa-solid fa-cloud-showers-water"></i>
          <span>Create Environmental Event</span>
        </a>
        <a href="environmental-events.html" class="sidebar__item">
          <i class="fa-solid fa-cloud-sun-rain"></i>
          <span>Environmental Events</span>
        </a>
      </div>
    </nav>

    <div class="sidebar__footer">
      <span>(c) GTAW Events Team 2026</span>
    </div>
  </aside>

  <main class="main">
    <header class="topbar">
      <button class="topbar__menu-btn" id="menuToggle">
        <i class="fa-solid fa-bars"></i>
      </button>

      <div class="topbar__right">
        <div class="topbar__clock" id="clock">21:24:48</div>
        <button class="icon-btn">
          <i class="fa-regular fa-bell"></i>
          <span class="icon-badge">0</span>
        </button>
        <div class="topbar__user">
          <div class="topbar__avatar">S</div>
          <div class="topbar__user-info">
            <div class="topbar__user-name">Unknown</div>
            <div class="topbar__user-role">Events Team</div>
          </div>
        </div>
      </div>
    </header>

    <section class="card environmental-form-card">
      <div class="card__header">
        <h2 id="env-form-title">Create Environmental Event</h2>
      </div>
      <div class="card__body">
        <form id="env-form" class="env-form-grid">
          <div>
            <label for="env-type">Event Type</label>
            <input type="text" id="env-type" placeholder="Storm, Heatwave, Flood..." />
          </div>
          <div>
            <label for="env-name">Event Name</label>
            <input type="text" id="env-name" placeholder="Name of event" required />
          </div>
          <div>
            <label for="env-district">Event District</label>
            <input type="text" id="env-district" placeholder="District of event" />
          </div>
          <div>
            <label for="env-event-id">Event ID</label>
            <input type="text" id="env-event-id" placeholder="In-game Event ID" />
          </div>
          <div>
            <label for="env-faction-flags">Faction Flags</label>
            <input type="text" id="env-faction-flags" placeholder="LEO,MED,LSGOV" />
          </div>
          <div>
            <label for="env-weight">Weight (1-10)</label>
            <input type="number" id="env-weight" min="1" max="10" value="5" required />
          </div>
          <div class="env-span-full">
            <label for="env-label">Label</label>
            <textarea id="env-label" placeholder="Label for the environmental event..."></textarea>
          </div>
          <div class="env-form-actions env-span-full">
            <button type="button" id="env-cancel-btn" class="env-btn env-btn-light">Cancel</button>
            <button type="submit" id="env-submit-btn" class="env-btn env-btn-primary">Create Event</button>
          </div>
        </form>
      </div>
    </section>

    <footer class="footer">
      <span>Made with love by Survivalmaster</span>
      <span class="footer__version">v0.0.1</span>
    </footer>
  </main>
</div>

<script src="js/script.js"></script>
<script src="js/environmental-events-wizard.js"></script>

<div id="usernameModal" class="username-modal hidden">
  <div class="username-modal__content">
    <h3>Set Your Username</h3>
    <input type="text" id="usernameInput" placeholder="Enter your name..." />
    <div class="username-modal__actions">
      <button id="usernameSaveBtn">Save</button>
      <button id="usernameCancelBtn">Cancel</button>
    </div>
  </div>
</div>

</body>
</html>
