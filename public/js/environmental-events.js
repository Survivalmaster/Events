document.addEventListener("DOMContentLoaded", async () => {
  const API_EVENTS = "api/environmental-events.php";

  const container = document.getElementById("events-container");
  const emptyEl = document.getElementById("events-empty");
  const commandsToggleBtn = document.getElementById("env-commands-toggle");
  const commandsPanel = document.getElementById("env-commands-panel");
  if (!container || !emptyEl) return;

  let events = [];
  let bannerModal = null;

  function copyText(text) {
    if (!text) return;
    if (navigator.clipboard?.writeText) {
      navigator.clipboard.writeText(text).catch(() => {});
      return;
    }

    const ta = document.createElement("textarea");
    ta.value = text;
    document.body.appendChild(ta);
    ta.select();
    try {
      document.execCommand("copy");
    } catch {}
    document.body.removeChild(ta);
  }

  if (commandsToggleBtn && commandsPanel) {
    commandsToggleBtn.addEventListener("click", () => {
      const isHidden = commandsPanel.classList.toggle("hidden");
      commandsToggleBtn.textContent = isHidden ? "Commands" : "Hide Commands";
    });

    commandsPanel.querySelectorAll("[data-copy-command]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const command = btn.getAttribute("data-copy-command") || "";
        copyText(command);
        const old = btn.textContent;
        btn.textContent = "Copied";
        setTimeout(() => {
          btn.textContent = old;
        }, 900);
      });
    });
  }

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

  async function loadEvents() {
    const data = await apiRequest(API_EVENTS);
    events = Array.isArray(data.events) ? data.events : [];
  }

  async function saveEvent(payload) {
    await apiRequest(API_EVENTS, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
  }

  async function deleteEvent(id) {
    await apiRequest(`${API_EVENTS}?id=${encodeURIComponent(id)}`, { method: "DELETE" });
  }

  function sanitizeText(value) {
    return String(value || "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;");
  }

  function getFactionFlags(ev) {
    const rawFlags = ev.factionFlags || ev.faction_flags || "";
    return String(rawFlags)
      .split(",")
      .map((flag) => flag.trim())
      .filter(Boolean);
  }

  function getFactionFlagClass(flag) {
    const normalized = String(flag || "").toUpperCase();

    if (normalized.includes("FIRE") || normalized.includes("SANFIRE")) return "faction-flag--fire";
    if (normalized.includes("LEO") || normalized.includes("POLICE") || normalized.includes("SHERIFF")) return "faction-flag--leo";
    if (normalized.includes("GOV")) return "faction-flag--gov";
    if (normalized.includes("EMS") || normalized.includes("MED") || normalized.includes("HOSPITAL")) return "faction-flag--medical";
    if (normalized.includes("DOT") || normalized.includes("WORKS")) return "faction-flag--works";

    return "faction-flag--default";
  }

  function renderFactionFlags(ev) {
    const flags = getFactionFlags(ev);

    if (!flags.length) {
      return '<span class="faction-flag faction-flag--empty">None</span>';
    }

    return flags
      .map((flag) => {
        const flagClass = getFactionFlagClass(flag);
        return `<span class="faction-flag ${flagClass}">${sanitizeText(flag)}</span>`;
      })
      .join("");
  }

  function getBannerValues(ev) {
    const bannerUrl = (ev.bannerUrl || ev.banner_url || "").trim();
    let bannerPosX = Number(ev.bannerPosX ?? ev.banner_pos_x ?? 50);
    let bannerPosY = Number(ev.bannerPosY ?? ev.banner_pos_y ?? 50);
    let bannerZoom = Number(ev.bannerZoom ?? ev.banner_zoom ?? 1);

    if (Number.isNaN(bannerPosX)) bannerPosX = 50;
    if (Number.isNaN(bannerPosY)) bannerPosY = 50;
    if (Number.isNaN(bannerZoom)) bannerZoom = 1;

    bannerPosX = Math.max(0, Math.min(100, bannerPosX));
    bannerPosY = Math.max(0, Math.min(100, bannerPosY));
    bannerZoom = Math.max(0.5, Math.min(3, bannerZoom));

    return { bannerUrl, bannerPosX, bannerPosY, bannerZoom };
  }

  function getWeight(ev) {
    const n = Number(ev.weight);
    if (Number.isNaN(n)) return 5;
    return Math.max(1, Math.min(10, Math.round(n)));
  }

  function getStatusBadge(ev) {
    const weight = getWeight(ev);
    let className = "status-weight-10";
    if (weight <= 3) className = "status-weight-3";
    else if (weight <= 5) className = "status-weight-5";
    else if (weight <= 7) className = "status-weight-7";
    return { label: `Weight ${weight}`, className };
  }

  function getActiveEvents() {
    return events;
  }

  function ensureBannerModal() {
    if (bannerModal) return bannerModal;

    const modal = document.createElement("div");
    modal.className = "banner-modal hidden";
    modal.innerHTML = `
      <div class="banner-modal__panel" role="dialog" aria-modal="true" aria-label="Manage banner">
        <h3>Banner Image</h3>
        <label for="banner-url-input">Image URL</label>
        <input id="banner-url-input" type="text" placeholder="https://i.imgur.com/example.png" />

        <div class="banner-modal__preview-wrap">
          <div class="banner-modal__preview" id="banner-preview-box">
            <img id="banner-preview-image" alt="Banner preview" />
            <div class="banner-modal__empty" id="banner-preview-empty">Add a direct image URL to preview.</div>
          </div>
        </div>

        <div class="banner-modal__sliders">
          <label for="banner-pos-x">Move Horizontal</label>
          <input id="banner-pos-x" type="range" min="0" max="100" step="1" value="50" />
          <label for="banner-pos-y">Move Vertical</label>
          <input id="banner-pos-y" type="range" min="0" max="100" step="1" value="50" />
          <label for="banner-zoom">Zoom</label>
          <input id="banner-zoom" type="range" min="0.5" max="3" step="0.01" value="1" />
        </div>

        <div class="banner-modal__actions">
          <button type="button" class="event-icon-btn" id="banner-remove-btn">Remove</button>
          <button type="button" class="event-icon-btn" id="banner-cancel-btn">Cancel</button>
          <button type="button" class="event-icon-btn event-icon-btn--primary" id="banner-save-btn">Save</button>
        </div>
      </div>
    `;

    document.body.appendChild(modal);
    bannerModal = modal;
    return bannerModal;
  }

  function openBannerEditor(ev, sourceCard) {
    const modal = ensureBannerModal();
    const urlInput = modal.querySelector("#banner-url-input");
    const posXInput = modal.querySelector("#banner-pos-x");
    const posYInput = modal.querySelector("#banner-pos-y");
    const zoomInput = modal.querySelector("#banner-zoom");
    const previewImage = modal.querySelector("#banner-preview-image");
    const previewEmpty = modal.querySelector("#banner-preview-empty");
    const previewBox = modal.querySelector("#banner-preview-box");
    const removeBtn = modal.querySelector("#banner-remove-btn");
    const cancelBtn = modal.querySelector("#banner-cancel-btn");
    const saveBtn = modal.querySelector("#banner-save-btn");

    const { bannerUrl, bannerPosX, bannerPosY, bannerZoom } = getBannerValues(ev);

    urlInput.value = bannerUrl;
    posXInput.value = String(bannerPosX);
    posYInput.value = String(bannerPosY);
    zoomInput.value = String(bannerZoom);

    const syncPreviewSize = () => {
      if (!previewBox) return;
      const sourceBanner = sourceCard?.querySelector?.(".event-card__banner");
      if (sourceBanner && sourceBanner.clientWidth > 0 && sourceBanner.clientHeight > 0) {
        previewBox.style.width = `${sourceBanner.clientWidth}px`;
        previewBox.style.height = `${sourceBanner.clientHeight}px`;
        return;
      }
      const cardWidth = sourceCard?.clientWidth || 260;
      const bannerWidth = Math.max(180, Math.round(cardWidth - 28));
      previewBox.style.width = `${bannerWidth}px`;
      previewBox.style.height = "92px";
    };

    const updatePreview = () => {
      const url = urlInput.value.trim();
      const x = Number(posXInput.value || 50);
      const y = Number(posYInput.value || 50);
      const zoom = Number(zoomInput.value || 1);
      previewImage.style.objectPosition = `${x}% ${y}%`;
      previewImage.style.transform = `scale(${zoom})`;

      if (!url) {
        previewImage.removeAttribute("src");
        previewEmpty.style.display = "flex";
        return;
      }

      previewImage.src = url;
      previewEmpty.style.display = "none";
    };

    const close = () => {
      modal.classList.add("hidden");
      removeBtn.onclick = null;
      cancelBtn.onclick = null;
      saveBtn.onclick = null;
      urlInput.oninput = null;
      posXInput.oninput = null;
      posYInput.oninput = null;
      zoomInput.oninput = null;
      modal.onclick = null;
    };

    const saveBanner = async (url, x, y, zoom) => {
      await saveEvent({
        id: ev.id,
        createdAt: ev.createdAt,
        event_id: ev.eventId || ev.event_id || "",
        faction_flags: ev.factionFlags || ev.faction_flags || "",
        weight: getWeight(ev),
        type: ev.type || "",
        name: ev.name || "",
        district: ev.district || "",
        banner_url: url,
        banner_pos_x: x,
        banner_pos_y: y,
        banner_zoom: zoom,
        label: ev.label || ev.description || "",
      });
      await loadEvents();
      render();
      close();
    };

    removeBtn.onclick = async () => {
      try {
        await saveBanner("", 50, 50, 1);
      } catch (error) {
        alert(`Could not remove banner: ${error.message}`);
      }
    };

    cancelBtn.onclick = close;

    saveBtn.onclick = async () => {
      const url = urlInput.value.trim();
      const x = Math.max(0, Math.min(100, Number(posXInput.value || 50)));
      const y = Math.max(0, Math.min(100, Number(posYInput.value || 50)));
      const zoom = Math.max(0.5, Math.min(3, Number(zoomInput.value || 1)));

      try {
        await saveBanner(url, x, y, zoom);
      } catch (error) {
        alert(`Could not save banner: ${error.message}`);
      }
    };

    urlInput.oninput = updatePreview;
    posXInput.oninput = updatePreview;
    posYInput.oninput = updatePreview;
    zoomInput.oninput = updatePreview;
    previewImage.onerror = () => { previewEmpty.style.display = "flex"; };
    modal.onclick = (e) => { if (e.target === modal) close(); };

    syncPreviewSize();
    updatePreview();
    modal.classList.remove("hidden");
  }

  function render() {
    container.innerHTML = "";
    const activeEvents = getActiveEvents();

    if (!activeEvents.length) {
      emptyEl.style.display = "block";
      return;
    }

    emptyEl.style.display = "none";

    activeEvents
      .slice()
      .sort((a, b) => Date.parse(b.createdAt || 0) - Date.parse(a.createdAt || 0))
      .forEach((ev) => {
        const card = document.createElement("article");
        card.className = "event-card";

        const badge = getStatusBadge(ev);
        const { bannerUrl, bannerPosX, bannerPosY, bannerZoom } = getBannerValues(ev);
        const bannerBlock = bannerUrl
          ? `
          <div class="event-card__banner">
            <img src="${sanitizeText(bannerUrl)}" alt="Event banner" loading="lazy" style="object-position: ${bannerPosX}% ${bannerPosY}%; transform: scale(${bannerZoom});" onerror="this.parentElement.style.display='none';" />
          </div>
        `
          : "";

        card.innerHTML = `
          <div class="event-card__header">
            <div class="event-card__header-left">
              <span class="event-status-pill ${badge.className}">${badge.label}</span>
              <button class="event-banner-manage" data-action="banner" title="Manage banner" aria-label="Manage banner">IMG</button>
            </div>
            <span>Event ID: ${ev.eventId || ev.event_id || "-"}</span>
          </div>
          ${bannerBlock}
          <div class="event-card__title">${ev.name || "Untitled event"}</div>
          <div class="event-card__desc">${ev.label || ev.description || ""}</div>
          <div class="event-card__flags" aria-label="Faction flags">
            <span class="event-card__flags-label">Faction Flags</span>
            <div class="event-card__flags-list">${renderFactionFlags(ev)}</div>
          </div>
          <div class="event-card__footer">
            <div class="event-card__icons-right">
              <button class="event-icon-btn" data-action="edit" title="Edit event">Edit</button>
              <button class="event-icon-btn" data-action="delete" title="Delete event">Delete</button>
            </div>
          </div>
        `;

        const bannerBtn = card.querySelector('[data-action="banner"]');
        const editBtn = card.querySelector('[data-action="edit"]');
        const deleteBtn = card.querySelector('[data-action="delete"]');

        if (bannerBtn) bannerBtn.addEventListener("click", () => openBannerEditor(ev, card));
        if (editBtn) {
          editBtn.addEventListener("click", () => {
            window.location.href = `environmental-events-wizard.html?id=${encodeURIComponent(ev.id)}`;
          });
        }
        if (deleteBtn) {
          deleteBtn.addEventListener("click", async () => {
            const ok = confirm("Are you sure you want to delete this event? This cannot be undone.");
            if (!ok) return;
            try {
              await deleteEvent(ev.id);
              await loadEvents();
              render();
            } catch (error) {
              alert(`Could not delete event: ${error.message}`);
            }
          });
        }

        container.appendChild(card);
      });
  }

  try {
    await loadEvents();
    render();
  } catch (error) {
    emptyEl.style.display = "block";
    emptyEl.textContent = `Could not load environmental events: ${error.message}`;
  }

  setInterval(async () => {
    try {
      await loadEvents();
      render();
    } catch {
      // keep current view when refresh fails
    }
  }, 30000);
});
