document.addEventListener("DOMContentLoaded", () => {
  const API_SETTINGS = "api/settings.php";

  const menuToggle = document.getElementById("menuToggle");
  const sidebar = document.querySelector(".sidebar");

  if (menuToggle && sidebar) {
    menuToggle.addEventListener("click", () => {
      sidebar.classList.toggle("sidebar--open");
    });
  }

  const clockEl = document.getElementById("clock");

  function updateClock() {
    if (!clockEl) return;
    const now = new Date();
    const h = String(now.getHours()).padStart(2, "0");
    const m = String(now.getMinutes()).padStart(2, "0");
    const s = String(now.getSeconds()).padStart(2, "0");
    clockEl.textContent = `${h}:${m}:${s}`;
  }

  if (clockEl) {
    updateClock();
    setInterval(updateClock, 1000);
  }

  if (document.querySelector(".topbar__logout")) {
    return;
  }

  const usernameModal = document.getElementById("usernameModal");
  const usernameInput = document.getElementById("usernameInput");
  const usernameSaveBtn = document.getElementById("usernameSaveBtn");
  const usernameCancelBtn = document.getElementById("usernameCancelBtn");

  const userNameDisplay = document.querySelector(".topbar__user-name");
  const userAvatar = document.querySelector(".topbar__avatar");
  const userArea = document.querySelector(".topbar__user");

  let currentUsername = "";

  async function apiRequest(url, options = {}) {
    const response = await fetch(url, options);
    let data = {};

    try {
      data = await response.json();
    } catch {
      data = {};
    }

    if (!response.ok) {
      const message = data.error || `Request failed (${response.status})`;
      throw new Error(message);
    }

    return data;
  }

  function openUsernameModal() {
    if (!usernameModal || !usernameInput) return;
    usernameInput.value = currentUsername;
    usernameModal.classList.remove("hidden");
    usernameInput.focus();
  }

  function closeUsernameModal() {
    if (!usernameModal) return;
    usernameModal.classList.add("hidden");
  }

  function applyUsername(name) {
    currentUsername = name;

    if (userNameDisplay) {
      userNameDisplay.textContent = name || "Unset";
    }

    if (userAvatar) {
      userAvatar.textContent = name ? name.charAt(0).toUpperCase() : "?";
    }
  }

  async function saveUsername() {
    if (!usernameInput) return;
    const name = usernameInput.value.trim();
    if (!name) return;

    await apiRequest(API_SETTINGS, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ key: "username", value: name }),
    });

    applyUsername(name);
    closeUsernameModal();
  }

  async function loadUsername() {
    const data = await apiRequest(API_SETTINGS);
    applyUsername(data.settings?.username || "");
  }

  if (userArea && usernameModal) {
    userArea.style.cursor = "pointer";
    userArea.addEventListener("click", openUsernameModal);
  }

  if (usernameSaveBtn) {
    usernameSaveBtn.addEventListener("click", async () => {
      try {
        await saveUsername();
      } catch (error) {
        alert(`Could not save username: ${error.message}`);
      }
    });
  }

  if (usernameCancelBtn) {
    usernameCancelBtn.addEventListener("click", closeUsernameModal);
  }

  if (usernameModal) {
    usernameModal.addEventListener("click", (e) => {
      if (e.target === usernameModal) {
        closeUsernameModal();
      }
    });
  }

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeUsernameModal();
    }
  });

  loadUsername().catch((error) => {
    applyUsername("");
    console.error(error);
  });
});
