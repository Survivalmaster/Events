window.addEventListener("DOMContentLoaded", () => {
  // --- Simple state / localStorage for events ---
  let events = [];
  const LS_KEY = "gtaw_events_portal_v1";
  const LS_HANDLER_KEY = "gtaw_events_handler";

  // Track if we are editing an existing event (null = creating new)
  let editingEventId = null;

  function loadEvents() {
    try {
      const raw = localStorage.getItem(LS_KEY);
      events = raw ? JSON.parse(raw) : [];
    } catch {
      events = [];
    }
    renderEvents();
  }

  function saveEvents() {
    try {
      localStorage.setItem(LS_KEY, JSON.stringify(events));
    } catch {
      // ignore
    }
  }

  // Helper to copy text to clipboard
  function copyText(text) {
    if (!text) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).catch(() => {});
    } else {
      const ta = document.createElement("textarea");
      ta.value = text;
      document.body.appendChild(ta);
      ta.select();
      try {
        document.execCommand("copy");
      } catch {
        // ignore
      }
      document.body.removeChild(ta);
    }
  }

  // ---- Status helpers ----
  const STATUS_ORDER = ["NEW", "SETUP", "CONCLUDED"];

  function getSavedStatus(ev) {
    return ev.status || "NEW";
  }

  function getDisplayStatus(ev) {
    const saved = getSavedStatus(ev);

    // If concluded, always show CONCLUDED
    if (saved === "CONCLUDED") return "CONCLUDED";

    // Auto LIVE when event is today (based on date only)
    if (ev.date) {
      const today = new Date();
      const todayStr = today.toISOString().slice(0, 10); // YYYY-MM-DD
      if (ev.date === todayStr) {
        return "LIVE";
      }
    }

    return saved;
  }

  function getNextSavedStatus(current) {
    const idx = STATUS_ORDER.indexOf(current);
    if (idx === -1) return "NEW";
    return STATUS_ORDER[(idx + 1) % STATUS_ORDER.length];
  }

  function renderEvents() {
    const listEl = document.getElementById("event-list");
    const emptyEl = document.getElementById("event-empty-state");
    listEl.innerHTML = "";

    if (!events.length) {
      emptyEl.classList.remove("hidden");
      return;
    }

    emptyEl.classList.add("hidden");

    events
      .slice()
      .sort((a, b) => (b.createdAt || 0) - (a.createdAt || 0))
      .forEach((ev) => {
        const card = document.createElement("article");
        card.className = "event-card";
        card.dataset.eventId = String(ev.id);

        const dateStr =
          ev.date && ev.time ? `${ev.date} (${ev.time})` : "Date not set";

        const displayStatus = getDisplayStatus(ev);
        const statusClass = `status-${displayStatus.toLowerCase()}`;

        card.innerHTML = `
          <div class="event-card-header">
            <span>📢 Event</span>
            <span>Property ID: ${ev.propertyId || "—"}</span>
          </div>
          <div class="event-card-title">${ev.name || "Untitled event"}</div>
          <div class="event-card-date">${dateStr}</div>
          <div class="event-card-handler">Handler: ${
            ev.handler || "Unknown"
          }</div>
          <div class="event-card-desc">${ev.description || ""}</div>
          <div class="event-card-footer">
            <div class="event-icons-left">
              <button class="icon-btn icon-discord" data-action="discord" title="Open Discord ticket">
                <svg viewBox="0 0 24 24" class="discord-svg">
                  <path
                    d="M20.317 4.369a19.791 19.791 0 00-4.885-1.515.074.074 0 00-.078.037c-.21.375-.444.864-.608 1.248a18.27 18.27 0 00-5.487 0 12.64 12.64 0 00-.617-1.248.077.077 0 00-.078-.037c-1.69.283-3.316.84-4.885 1.515a.07.07 0 00-.032.027C.533 9.045-.32 13.58.099 18.057a.082.082 0 00.031.057 19.9 19.9 0 006.031 3.042.077.077 0 00.084-.028c.464-.63.875-1.295 1.226-1.994a.076.076 0 00-.041-.104 13.1 13.1 0 01-1.872-.878.076.076 0 01-.008-.127c.126-.094.252-.192.372-.291a.074.074 0 01.077-.01c3.927 1.793 8.18 1.793 12.061 0a.074.074 0 01.078.009c.12.099.246.198.372.292a.076.076 0 01-.007.127c-.6.35-1.234.635-1.873.878a.076.076 0 00-.04.105c.36.699.771 1.365 1.225 1.993a.076.076 0 00.084.028 19.9 19.9 0 006.032-3.042.076.076 0 00.031-.056c.5-5.177-.838-9.682-3.548-13.662a.061.061 0 00-.031-.03zM8.02 15.334c-1.182 0-2.157-1.08-2.157-2.408 0-1.327.955-2.408 2.157-2.408 1.213 0 2.177 1.09 2.157 2.408 0 1.328-.955 2.408-2.157 2.408zm7.974 0c-1.182 0-2.157-1.08-2.157-2.408 0-1.327.955-2.408 2.157-2.408 1.213 0 2.178 1.09 2.157 2.408 0 1.328-.944 2.408-2.157 2.408z"
                    fill="currentColor"
                  />
                </svg>
              </button>
              <button class="icon-btn" data-action="poster" title="Copy /addevent command">🖼️</button>
            </div>
            <div class="event-icons-right">
              <button class="icon-btn" data-action="edit" title="Edit event">✏️</button>
              <button class="icon-btn" data-action="delete" title="Delete event">🗑️</button>
              <button class="status-pill ${statusClass}" data-action="status">${displayStatus}</button>
            </div>
          </div>
        `;

        const discordBtn = card.querySelector('[data-action="discord"]');
        const posterBtn = card.querySelector('[data-action="poster"]');
        const statusBtn = card.querySelector('[data-action="status"]');
        const editBtn = card.querySelector('[data-action="edit"]');
        const deleteBtn = card.querySelector('[data-action="delete"]');

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
            // /addevent name:NAME datetime:YYYY-MM-DD HH:MM description:DESCRIPTION image:
            const name = ev.name || "";
            let datetime = "";
            if (ev.date && ev.time) {
              datetime = `${ev.date} ${ev.time}`;
            } else if (ev.date) {
              datetime = ev.date;
            }
            const desc = ev.description || "";
            const cmd = `/addevent name:${name} datetime:${datetime} description:${desc} image:`;
            copyText(cmd);

            const original = posterBtn.textContent;
            posterBtn.textContent = "✅";
            setTimeout(() => {
              posterBtn.textContent = original;
            }, 800);
          });
        }

        if (statusBtn) {
          statusBtn.addEventListener("click", () => {
            const idx = events.findIndex((e) => e.id === ev.id);
            if (idx === -1) return;
            const currentSaved = getSavedStatus(events[idx]);
            const next = getNextSavedStatus(currentSaved);
            events[idx].status = next;
            saveEvents();
            renderEvents();
          });
        }

        if (editBtn) {
          editBtn.addEventListener("click", () => {
            editingEventId = ev.id;
            loadEventIntoForm(ev);
            setStage(1);
          });
        }

        if (deleteBtn) {
          deleteBtn.addEventListener("click", () => {
            const ok = confirm(
              "Are you sure you want to delete this event? This cannot be undone."
            );
            if (!ok) return;
            events = events.filter((e) => e.id !== ev.id);
            saveEvents();
            renderEvents();

            // If we were editing this event, reset the form/editor
            if (editingEventId === ev.id) {
              resetForm();
            }
          });
        }

        listEl.appendChild(card);
      });
  }

  // --- Handler settings helpers ---
  const handlerField = document.getElementById("handler");

  function getStoredHandler() {
    try {
      return localStorage.getItem(LS_HANDLER_KEY) || "";
    } catch {
      return "";
    }
  }

  function setStoredHandler(value) {
    try {
      localStorage.setItem(LS_HANDLER_KEY, value || "");
    } catch {
      // ignore
    }
  }

  function applyStoredHandlerToField() {
    const value = getStoredHandler();
    handlerField.value = value;
  }

  // --- Wizard logic ---
  let currentStage = 1;
  const totalStages = 3;

  const stageEls = Array.from(document.querySelectorAll(".wizard-stage"));
  const stagePills = Array.from(document.querySelectorAll(".stage-pill"));

  const btnBack = document.getElementById("btn-back");
  const btnNext = document.getElementById("btn-next");
  const btnCancel = document.getElementById("btn-cancel");

  function setStage(stage) {
    currentStage = stage;

    stageEls.forEach((el) => {
      const s = Number(el.dataset.stage);
      el.classList.toggle("hidden", s !== stage);
    });

    stagePills.forEach((pill) => {
      const label = Number(pill.dataset.stageLabel);
      pill.classList.toggle("active", label === stage);
      pill.classList.toggle("done", label < stage);
    });

    btnBack.disabled = stage === 1;
    btnNext.textContent =
      stage === totalStages
        ? editingEventId
          ? "Save Changes"
          : "Submit Event"
        : "Next";
  }

  function clearErrors() {
    document
      .querySelectorAll(".error-text")
      .forEach((el) => el.classList.add("hidden"));
  }

  function validateStage1() {
    clearErrors();
    let valid = true;
    const requiredIds = [
      "handler",
      "event-type",
      "event-date",
      "event-time",
      "event-name",
      "property-id", // make Property ID mandatory before Stage 2
    ];

    requiredIds.forEach((id) => {
      const el = document.getElementById(id);
      if (!el.value.trim()) {
        valid = false;
        const err = document.querySelector(
          `.error-text[data-error-for="${id}"]`
        );
        if (err) err.classList.remove("hidden");
      }
    });

    return valid;
  }

  function validateBeforeSubmit() {
    clearErrors();
    const propertyId = document.getElementById("property-id").value.trim();
    if (!propertyId) {
      const err = document.querySelector(
        `.error-text[data-error-for="property-id"]`
      );
      if (err) err.classList.remove("hidden");
      return false;
    }
    return true;
  }

  function collectEventFromForm() {
    return {
      id: editingEventId || Date.now(),
      createdAt: Date.now(),
      handler: document.getElementById("handler").value.trim(),
      type: document.getElementById("event-type").value.trim(),
      date: document.getElementById("event-date").value,
      time: document.getElementById("event-time").value,
      name: document.getElementById("event-name").value.trim(),
      district: document.getElementById("event-district").value.trim(),
      discord: document.getElementById("discord-link").value.trim(),
      description: document
        .getElementById("event-description")
        .value.trim(),
      propertyId: document.getElementById("property-id").value.trim(),
      notes: document.getElementById("notes").value.trim(),
      status: "NEW", // default for NEW events; edits will keep old status
    };
  }

  function loadEventIntoForm(ev) {
    document.getElementById("handler").value = ev.handler || "";
    document.getElementById("event-type").value = ev.type || "";
    document.getElementById("event-date").value = ev.date || "";
    document.getElementById("event-time").value = ev.time || "";
    document.getElementById("event-name").value = ev.name || "";
    document.getElementById("event-district").value = ev.district || "";
    document.getElementById("discord-link").value = ev.discord || "";
    document.getElementById("event-description").value =
      ev.description || "";
    document.getElementById("property-id").value = ev.propertyId || "";
    document.getElementById("notes").value = ev.notes || "";

    clearErrors();
    updateCreateCommand();
    updateSetdimCommand();
  }

  function resetForm() {
    document
      .querySelectorAll(
        "input[type='text'], input[type='date'], input[type='time'], textarea"
      )
      .forEach((el) => {
        if (el.id.startsWith("cmd-")) return; // keep default commands
        el.value = "";
      });
    document.getElementById("event-type").value = "";
    document.getElementById("own-ingame").checked = true;
    document.getElementById("own-offline").checked = false;
    toggleOwnerBlocks();
    clearErrors();
    editingEventId = null; // back to "new event" mode
    applyStoredHandlerToField();
    updateCreateCommand();
    updateSetdimCommand();
    setStage(1);
  }

  // Copy buttons (wizard)
  document.querySelectorAll(".btn-copy").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.copyFrom;
      const input = document.getElementById(id);
      if (!input) return;
      const text = input.value;

      copyText(text);

      const original = btn.textContent;
      btn.textContent = "Copied!";
      setTimeout(() => (btn.textContent = original), 800);
    });
  });

  // Owner blocks show/hide
  const ownIngame = document.getElementById("own-ingame");
  const ownOffline = document.getElementById("own-offline");
  const ownerIngameBlock = document.getElementById("owner-ingame-block");
  const ownerOfflineBlock = document.getElementById("owner-offline-block");

  function toggleOwnerBlocks() {
    ownerIngameBlock.classList.toggle("hidden", !ownIngame.checked);
    ownerOfflineBlock.classList.toggle("hidden", !ownOffline.checked);
  }

  ownIngame.addEventListener("change", toggleOwnerBlocks);
  ownOffline.addEventListener("change", toggleOwnerBlocks);

  // --- Stage 2 dynamic commands ---

  const playerIdField = document.getElementById("player-id");
  const cmdPownerField = document.getElementById("cmd-powner");

  playerIdField.addEventListener("input", () => {
    const num = playerIdField.value.trim();
    cmdPownerField.value = num ? `/powner ${num}` : "/powner";
  });

  const charNameField = document.getElementById("char-name");
  const cmdGciofflineField = document.getElementById("cmd-gcioffline");

  charNameField.addEventListener("input", () => {
    const name = charNameField.value.trim();
    cmdGciofflineField.value = name ? `/gcioffline ${name}` : "/gcioffline";
  });

  const ucpIdField = document.getElementById("ucp-id");
  const cmdPownerofflineField = document.getElementById("cmd-powneroffline");

  ucpIdField.addEventListener("input", () => {
    const id = ucpIdField.value.trim();
    cmdPownerofflineField.value = id
      ? `/powneroffline 1 ${id}`
      : "/powneroffline 1";
  });

  // /setdim with admin ID + Property ID
  const adminIdField = document.getElementById("admin-id");
  const cmdSetdimField = document.getElementById("cmd-setdim");
  const propertyIdField = document.getElementById("property-id");

  function updateSetdimCommand() {
    const adminId = adminIdField.value.trim();
    const propId = propertyIdField.value.trim();

    let cmd = "/setdim";

    if (adminId) {
      cmd += " " + adminId;
    }
    if (propId) {
      cmd += " " + propId;
    }

    cmdSetdimField.value = cmd;
  }

  adminIdField.addEventListener("input", updateSetdimCommand);
  propertyIdField.addEventListener("input", updateSetdimCommand);

  // Dynamic /createproperty command (Stage 1)
  const eventTypeField = document.getElementById("event-type");
  const eventNameField = document.getElementById("event-name");
  const eventDistrictField = document.getElementById("event-district");
  const createCommandField = document.getElementById("create-command");

  function updateCreateCommand() {
    let type = eventTypeField.value || "2"; // default to 2
    const name = eventNameField.value.trim();
    const district = eventDistrictField.value.trim();

    let suffix = "[EVENT]";
    if (name) {
      suffix += " " + name;
    }
    if (district) {
      suffix += " - " + district;
    }

    createCommandField.value = `/createproperty ${type} 0 200 0 1 M ${suffix}`;
  }

  eventTypeField.addEventListener("change", updateCreateCommand);
  eventNameField.addEventListener("input", updateCreateCommand);
  eventDistrictField.addEventListener("input", updateCreateCommand);

  // Buttons
  btnBack.addEventListener("click", () => {
    if (currentStage > 1) setStage(currentStage - 1);
  });

  btnNext.addEventListener("click", () => {
    if (currentStage === 1) {
      if (!validateStage1()) return;
      setStage(2);
      return;
    }

    if (currentStage === 2) {
      setStage(3);
      return;
    }

    // Stage 3 -> submit / save
    if (!validateBeforeSubmit()) return;
    const formEvent = collectEventFromForm();

    if (editingEventId) {
      const idx = events.findIndex((e) => e.id === editingEventId);
      if (idx !== -1) {
        const existing = events[idx];
        const updated = {
          ...existing,
          ...formEvent,
          id: existing.id,
          createdAt: existing.createdAt,
          status: existing.status || "NEW", // keep current status
        };
        events[idx] = updated;
      }
    } else {
      events.push(formEvent);
    }

    saveEvents();
    renderEvents();
    resetForm();
  });

  btnCancel.addEventListener("click", resetForm);

  // Clicking stage pills (allow going backwards)
  stagePills.forEach((pill) => {
    pill.addEventListener("click", () => {
      const n = Number(pill.dataset.stageLabel);
      if (n <= currentStage) {
        setStage(n);
      }
    });
  });

  // --- Settings modal logic ---
  const settingsModal = document.getElementById("settings-modal");
  const btnOpenSettings = document.getElementById("btn-open-settings");
  const btnSettingsClose = document.getElementById("btn-settings-close");
  const btnSettingsCancel = document.getElementById("btn-settings-cancel");
  const btnSettingsSave = document.getElementById("btn-settings-save");
  const settingsHandlerInput = document.getElementById("settings-handler-input");

  function openSettingsModal() {
    settingsHandlerInput.value = getStoredHandler();
    settingsModal.classList.remove("hidden");
    settingsHandlerInput.focus();
  }

  function closeSettingsModal() {
    settingsModal.classList.add("hidden");
  }

  btnOpenSettings.addEventListener("click", openSettingsModal);
  btnSettingsClose.addEventListener("click", closeSettingsModal);
  btnSettingsCancel.addEventListener("click", closeSettingsModal);

  btnSettingsSave.addEventListener("click", () => {
    const value = settingsHandlerInput.value.trim();
    setStoredHandler(value);
    handlerField.value = value; // fully overwrite current event handler
    closeSettingsModal();
  });

  // Close modal on backdrop click
  settingsModal.addEventListener("click", (e) => {
    if (e.target === settingsModal) {
      closeSettingsModal();
    }
  });

  // Close modal on Escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !settingsModal.classList.contains("hidden")) {
      closeSettingsModal();
    }
  });

  // Initial
  loadEvents();
  applyStoredHandlerToField();
  updateCreateCommand();
  updateSetdimCommand();
  setStage(1);
});
