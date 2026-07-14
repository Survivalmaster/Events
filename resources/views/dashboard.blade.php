<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Events Portal - Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link rel="icon" type="image/png" href="assets/favicon.png">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Icons (optional) -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />

  <link rel="stylesheet" href="css/styles.css" />
</head>
<body>
@php
  $authUser = session('discord_user', []);
  $displayName = $authUser['display_name'] ?? $authUser['username'] ?? 'Events Team';
  $avatarUrl = $authUser['avatar'] ?? null;
  $envStats = $envStats ?? [
    'total' => 0,
    'veryRare' => 0,
    'rare' => 0,
    'common' => 0,
    'overview' => 'No environmental event stats are available yet.',
  ];
@endphp
<div class="app">


<!-- !!! NAVIGATION AREA !!! -->

  <!-- Sidebar Top -->
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

    <!-- Sidebar Top -->
    <nav class="sidebar__nav">
      <div class="sidebar__section">
        <div class="sidebar__section-title">Navigation</div>
        <a href="index.html" class="sidebar__item sidebar__item--active">
          <i class="fa-solid fa-gauge-high"></i>
          <span>Dashboard</span>
        </a>
      </div>

      <div class="sidebar__section">
        <div class="sidebar__section-title">Sections</div>
        <a href="environmental-events-wizard.html" class="sidebar__item">
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
      <span>© GTAW Events Team 2026</span>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main">
    <!-- Topbar -->
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
          <div class="topbar__avatar">
            @if ($avatarUrl)
              <img src="{{ $avatarUrl }}" alt="{{ $displayName }}">
            @else
              {{ mb_substr($displayName, 0, 1) }}
            @endif
          </div>
          <div class="topbar__user-info">
            <div class="topbar__user-name">{{ $displayName }}</div>
            <div class="topbar__user-role">Events Team</div>
          </div>
        </div>
        <form method="POST" action="{{ route('discord.logout') }}">
          @csrf
          <button class="topbar__logout" type="submit">Logout</button>
        </form>
      </div>
    </header>

    <section class="stats">
      <article class="stats-card">
        <div class="stats-card__header">
          <div class="stats-card__title">Environmental Templates</div>
        </div>
        <div class="stats-card__body">
          <div class="stats-card__value" id="env-stat-total">{{ $envStats['total'] }}</div>
          <button class="stats-card__action">
            <i class="fa-solid fa-cloud-sun-rain"></i>
          </button>
        </div>
        <a href="environmental-events.html" class="stats-card__link">View Environmental Events</a>
      </article>

      <article class="stats-card">
        <div class="stats-card__header">
          <div class="stats-card__title">Very Rare (Weight 1-3)</div>
        </div>
        <div class="stats-card__body">
          <div class="stats-card__value" id="env-stat-rare">{{ $envStats['veryRare'] }}</div>
          <button class="stats-card__action stats-card__action--alert">
            <i class="fa-solid fa-triangle-exclamation"></i>
          </button>
        </div>
        <a href="environmental-events.html" class="stats-card__link">Review rare templates</a>
      </article>

      <article class="stats-card">
        <div class="stats-card__header">
          <div class="stats-card__title">Rare (Weight 4-7)</div>
        </div>
        <div class="stats-card__body">
          <div class="stats-card__value" id="env-stat-medium">{{ $envStats['rare'] }}</div>
          <button class="stats-card__action stats-card__action--primary">
            <i class="fa-solid fa-seedling"></i>
          </button>
        </div>
        <a href="environmental-events.html" class="stats-card__link">Review medium templates</a>
      </article>

      <article class="stats-card">
        <div class="stats-card__header">
          <div class="stats-card__title">Common (Weight 8-10)</div>
        </div>
        <div class="stats-card__body">
          <div class="stats-card__value" id="env-stat-common">{{ $envStats['common'] }}</div>
          <button class="stats-card__action">
            <i class="fa-solid fa-cloud-sun"></i>
          </button>
        </div>
        <a href="environmental-events.html" class="stats-card__link">Review common templates</a>
      </article>
    </section>

    <!-- Main Card -->
    <section class="card card--stretch outstanding-tasks">
      <div class="card__header">
        <h2>Environmental Events Overview</h2>
      </div>
      <div class="card__body">
        <p id="env-overview-text">{{ $envStats['overview'] }}</p>
      </div>
    </section>

    <footer class="footer">
      <span>Made with ❤️ by Survivalmaster</span>
      <span class="footer__version">v0.0.1</span>
    </footer>
  </main>
</div>

<script src="js/script.js"></script>

<!-- Username Popup Modal -->
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
