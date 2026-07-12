document.addEventListener("DOMContentLoaded", async () => {
  const API_EVENTS = "api/events.php";

  const container = document.getElementById("events-container");
  const emptyEl = document.getElementById("events-empty");
  if (!container) return;

  let events = [];
  const warmedImageUrls = new Set();
  let bannerModal = null;

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
    warmExistingImages(events);
  }

  async function deleteEvent(id) {
    await apiRequest(`${API_EVENTS}?id=${encodeURIComponent(id)}`, {
      method: "DELETE",
    });
  }

  async function saveEvent(eventData) {
    await apiRequest(API_EVENTS, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(eventData),
    });
  }

  function getSavedStatus(ev) {
    return ev.status || "NEW";
  }

  function getDisplayStatus(ev) {
    const saved = getSavedStatus(ev);

    if (saved === "CONCLUDED") return "CONCLUDED";

    if (ev.date) {
      const todayStr = new Date().toISOString().slice(0, 10);
      if (ev.date === todayStr) return "LIVE";
    }

    return saved;
  }

  function sanitizeText(value) {
    return String(value || "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;");
  }

  function getCachedImageUrl(url) {
    return `api/image-cache.php?url=${encodeURIComponent(url)}`;
  }

  function getBannerUrl(ev) {
    return (ev.bannerUrl || ev.banner_url || "").trim();
  }

  function warmExistingImages(sourceEvents) {
    const urls = [...new Set(sourceEvents.map(getBannerUrl).filter(Boolean))]
      .filter((url) => !warmedImageUrls.has(url));

    if (!urls.length) return;

    urls.forEach((url) => warmedImageUrls.add(url));

    let nextIndex = 0;
    const workerCount = Math.min(4, urls.length);

    const warmNext = async () => {
      const url = urls[nextIndex];
      nextIndex += 1;

      if (!url) return;

      try {
        await fetch(getCachedImageUrl(url), { cache: "force-cache" });
      } catch {
        // The card image itself will still try to load when visible.
      }

      await warmNext();
    };

    for (let i = 0; i < workerCount; i += 1) {
      warmNext();
    }
  }

  function getBannerValues(ev) {
    const bannerUrl = getBannerUrl(ev);
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

    removeBtn.onclick = async () => {
      try {
        await saveEvent({
          id: ev.id,
          status: ev.status || "NEW",
          handler: ev.handler || "",
          type: ev.type || "",
          date: ev.date || "",
          time: ev.time || "",
          name: ev.name || "",
          district: ev.district || "",
          discord: ev.discord || "",
          banner_url: "",
          banner_pos_x: 50,
          banner_pos_y: 50,
          banner_zoom: 1,
          description: ev.description || "",
          property_id: ev.propertyId || ev.property_id || "",
          notes: ev.notes || "",
        });
        await loadEvents();
        render();
        close();
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
        await saveEvent({
          id: ev.id,
          status: ev.status || "NEW",
          handler: ev.handler || "",
          type: ev.type || "",
          date: ev.date || "",
          time: ev.time || "",
          name: ev.name || "",
          district: ev.district || "",
          discord: ev.discord || "",
          banner_url: url,
          banner_pos_x: x,
          banner_pos_y: y,
          banner_zoom: zoom,
          description: ev.description || "",
          property_id: ev.propertyId || ev.property_id || "",
          notes: ev.notes || "",
        });
        await loadEvents();
        render();
        close();
      } catch (error) {
        alert(`Could not save banner: ${error.message}`);
      }
    };

    urlInput.oninput = updatePreview;
    posXInput.oninput = updatePreview;
    posYInput.oninput = updatePreview;
    zoomInput.oninput = updatePreview;

    previewImage.onerror = () => {
      previewEmpty.style.display = "flex";
    };

    modal.onclick = (e) => {
      if (e.target === modal) close();
    };

    syncPreviewSize();
    updatePreview();
    modal.classList.remove("hidden");
  }

  function render() {
    container.innerHTML = "";

    if (!events.length) {
      emptyEl.style.display = "block";
      return;
    }

    emptyEl.style.display = "none";

    events
      .slice()
      .sort((a, b) => Date.parse(b.createdAt || 0) - Date.parse(a.createdAt || 0))
      .forEach((ev) => {
        const card = document.createElement("article");
        card.className = "event-card";

        const dateStr =
          ev.date && ev.time
            ? `${ev.date} (${ev.time})`
            : ev.date
            ? ev.date
            : "Date not set";

        const displayStatus = getDisplayStatus(ev);
        const statusClass = `status-${displayStatus.toLowerCase()}`;
        const { bannerUrl, bannerPosX, bannerPosY, bannerZoom } = getBannerValues(ev);
        const bannerBlock = bannerUrl
          ? `
          <div class="event-card__banner">
            <img src="${sanitizeText(getCachedImageUrl(bannerUrl))}" data-source-url="${sanitizeText(bannerUrl)}" alt="Event banner" loading="lazy" decoding="async" style="object-position: ${bannerPosX}% ${bannerPosY}%; transform: scale(${bannerZoom});" onerror="if (!this.dataset.sourceTried) { this.dataset.sourceTried = '1'; this.src = this.dataset.sourceUrl; } else { this.parentElement.style.display = 'none'; }" />
          </div>
        `
          : "";

        card.innerHTML = `
          <div class="event-card__header">
            <div class="event-card__header-left">
              <span class="event-status-pill ${statusClass}" data-action="status">${displayStatus}</span>
              <button class="event-banner-manage" data-action="banner" title="Manage banner" aria-label="Manage banner">IMG</button>
            </div>
            <span>Property ID: ${ev.propertyId || ev.property_id || "-"}</span>
          </div>
          ${bannerBlock}
          <div class="event-card__title">${ev.name || "Untitled event"}</div>
          <div class="event-card__date">${dateStr}</div>
          <div class="event-card__handler">Handler: ${ev.handler || "Unknown"}</div>
          <div class="event-card__desc">${ev.description || ""}</div>
          <div class="event-card__footer">
            <div class="event-card__icons-left">
              <button class="event-icon-btn" data-action="discord" title="Open Discord ticket">Discord</button>
              <button class="event-icon-btn" data-action="poster" title="Copy /addevent command">Poster</button>
            </div>
            <div class="event-card__icons-right">
              <button class="event-icon-btn" data-action="edit" title="Edit event">Edit</button>
              <button class="event-icon-btn" data-action="delete" title="Delete event">Delete</button>
            </div>
          </div>
        `;

        const bannerBtn = card.querySelector('[data-action="banner"]');
        const discordBtn = card.querySelector('[data-action="discord"]');
        const posterBtn = card.querySelector('[data-action="poster"]');
        const editBtn = card.querySelector('[data-action="edit"]');
        const deleteBtn = card.querySelector('[data-action="delete"]');

        if (bannerBtn) {
          bannerBtn.addEventListener("click", () => openBannerEditor(ev, card));
        }

        if (discordBtn) {
          discordBtn.addEventListener("click", () => {
            if (ev.discord) {
              window.open(ev.discord, "_blank", "noopener");
            } else {
              alert("No Discord link was saved for this event.");
            }
          });
        }

        if (posterBtn) {
          posterBtn.addEventListener("click", () => {
            const name = ev.name || "";
            const datetime = ev.date && ev.time ? `${ev.date} ${ev.time}` : ev.date || "";
            const desc = ev.description || "";
            const cmd = `/addevent name:${name} datetime:${datetime} description:${desc} image:`;

            if (navigator.clipboard?.writeText) {
              navigator.clipboard.writeText(cmd).catch(() => {});
            }

            const original = posterBtn.textContent;
            posterBtn.textContent = "Copied";
            setTimeout(() => {
              posterBtn.textContent = original;
            }, 800);
          });
        }

        if (editBtn) {
          editBtn.addEventListener("click", () => {
            window.location.href = `events-wizard.html?id=${encodeURIComponent(ev.id)}`;
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
    emptyEl.textContent = `Could not load events: ${error.message}`;
  }
});
