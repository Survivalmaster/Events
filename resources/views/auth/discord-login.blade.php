<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Events Portal - Discord Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="assets/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="css/environmental-events.css" />
</head>
<body class="auth-page">
  <main class="discord-login">
    <section class="discord-login__panel">
      <div class="discord-login__brand">
        <img src="assets/logo.png" alt="Events Team" />
        <span>Events Team Portal</span>
      </div>

      <h1>Sign in with Discord</h1>
      <p>Access is limited to members of the Events Team role in the Events Team Discord.</p>

      @if ($error)
        <div class="discord-login__error">{{ $error }}</div>
      @endif

      @if ($isConfigured)
        <a class="discord-login__button" href="{{ route('discord.redirect') }}">
          <span class="discord-login__icon">D</span>
          Continue with Discord
        </a>
      @else
        <div class="discord-login__error">
          Discord OAuth is missing its client ID, client secret, or redirect URL in .env.
        </div>
      @endif
    </section>
  </main>
</body>
</html>
