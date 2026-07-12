document.addEventListener("DOMContentLoaded", async () => {
  const API_ENV_EVENTS = "api/environmental-events.php";

  const totalEl = document.getElementById("env-stat-total");
  const rareEl = document.getElementById("env-stat-rare");
  const mediumEl = document.getElementById("env-stat-medium");
  const commonEl = document.getElementById("env-stat-common");
  const overviewEl = document.getElementById("env-overview-text");

  if (!totalEl || !rareEl || !mediumEl || !commonEl) return;

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

  function getWeight(eventItem) {
    const n = Number(eventItem?.weight ?? 0);
    if (Number.isNaN(n)) return 0;
    return Math.max(1, Math.min(10, Math.round(n)));
  }

  function setFallback(message) {
    totalEl.textContent = "--";
    rareEl.textContent = "--";
    mediumEl.textContent = "--";
    commonEl.textContent = "--";
    if (overviewEl) overviewEl.textContent = message;
  }

  try {
    const data = await apiRequest(API_ENV_EVENTS);
    const events = Array.isArray(data.events) ? data.events : [];

    const total = events.length;
    const weights = events.map(getWeight).filter((w) => w > 0);
    const rare = weights.filter((w) => w <= 3).length;
    const medium = weights.filter((w) => w >= 4 && w <= 7).length;
    const common = weights.filter((w) => w >= 8).length;

    totalEl.textContent = String(total);
    rareEl.textContent = String(rare);
    mediumEl.textContent = String(medium);
    commonEl.textContent = String(common);

    if (overviewEl) {
      overviewEl.textContent = `Currently tracking ${total} environmental templates. ${rare} are very rare (1-3), ${medium} are rare (4-7), and ${common} are common (8-10).`;
    }
  } catch (error) {
    setFallback(`Could not load environmental stats: ${error.message}`);
  }
});
