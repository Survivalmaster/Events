document.addEventListener("DOMContentLoaded", async () => {
  const API_EVENTS = "api/events.php";
  const API_SETTINGS = "api/settings.php";

  let events = [];
  let editingEventId = null;
  let settings = { username: "", handler: "" };

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

  async function loadSettings() {
    const data = await apiRequest(API_SETTINGS);
    settings = {
      username: data.settings?.username || "",
      handler: data.settings?.handler || "",
    };
  }

  async function saveSetting(key, value) {
    await apiRequest(API_SETTINGS, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ key, value }),
    });
  }

  async function saveEvent(eventPayload) {
    return apiRequest(API_EVENTS, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(eventPayload),
    });
  }

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

  const handlerField = document.getElementById("handler");

  function getGlobalUsername() {
    return settings.username || "";
  }

  function getStoredHandler() {
    return settings.handler || "";
  }

  async function setStoredHandler(value) {
    settings.handler = value || "";
    try {
      await saveSetting("handler", settings.handler);
    } catch {
      // non-fatal
    }
  }

  function applyStoredHandlerToField() {
    if (!handlerField) return;

    const globalUsername = getGlobalUsername();
    const storedHandler = getStoredHandler();
    const valueToUse = globalUsername || storedHandler;

    if (!handlerField.value.trim() && valueToUse) {
      handlerField.value = valueToUse;
    }
  }

  if (handlerField) {
    handlerField.addEventListener("blur", async () => {
      const value = handlerField.value.trim();
      if (value) await setStoredHandler(value);
    });
  }

  let currentStage = 1;
  const totalStages = 3;

  const stageEls = [...document.querySelectorAll(".wizard-stage")];
  const stagePills = [...document.querySelectorAll(".stage-pill")];

  const btnBack = document.getElementById("btn-back");
  const btnNext = document.getElementById("btn-next");
  const btnCancel = document.getElementById("btn-cancel");

  function setStage(stage) {
    currentStage = stage;

    stageEls.forEach((el) => {
      el.classList.toggle("hidden", Number(el.dataset.stage) !== stage);
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
    document.querySelectorAll(".error-text").forEach((el) => {
      el.classList.add("hidden");
    });
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
    ];

    if (!editingEventId) requiredIds.push("property-id");

    requiredIds.forEach((id) => {
      const el = document.getElementById(id);
      if (!el || !el.value.trim()) {
        valid = false;
        const err = document.querySelector(`.error-text[data-error-for="${id}"]`);
        if (err) err.classList.remove("hidden");
      }
    });

    return valid;
  }

  function validateBeforeSubmit() {
    clearErrors();
    const propertyId = (document.getElementById("property-id") || {}).value?.trim?.() || "";

    if (!propertyId) {
      const err = document.querySelector('.error-text[data-error-for="property-id"]');
      if (err) err.classList.remove("hidden");
      return false;
    }
    return true;
  }

  const eventTypeField = document.getElementById("event-type");
  const eventNameField = document.getElementById("event-name");
  const eventDistrictField = document.getElementById("event-district");
  const createCommandField = document.getElementById("create-command");

  function updateCreateCommand() {
    if (!createCommandField) return;

    const type = eventTypeField?.value || "2";
    const name = eventNameField?.value.trim() || "";
    const district = eventDistrictField?.value.trim() || "";

    let suffix = "[EVENT]";
    if (name) suffix += ` ${name}`;
    if (district) suffix += ` - ${district}`;

    createCommandField.value = `/createproperty ${type} 0 200 0 1 M ${suffix}`;
  }

  eventTypeField?.addEventListener("change", updateCreateCommand);
  eventNameField?.addEventListener("input", updateCreateCommand);
  eventDistrictField?.addEventListener("input", updateCreateCommand);

  const adminIdField = document.getElementById("admin-id");
  const cmdSetdimField = document.getElementById("cmd-setdim");
  const propertyIdField = document.getElementById("property-id");

  function updateSetdimCommand() {
    if (!cmdSetdimField) return;

    const adminId = adminIdField?.value.trim() || "";
    const propId = propertyIdField?.value.trim() || "";

    let cmd = "/setdim";
    if (adminId) cmd += ` ${adminId}`;
    if (propId) cmd += ` ${propId}`;

    cmdSetdimField.value = cmd;
  }

  adminIdField?.addEventListener("input", updateSetdimCommand);

  const ownIngame = document.getElementById("own-ingame");
  const ownOffline = document.getElementById("own-offline");
  const ownerIngameBlock = document.getElementById("owner-ingame-block");
  const ownerOfflineBlock = document.getElementById("owner-offline-block");

  function toggleOwnerBlocks() {
    if (ownerIngameBlock) ownerIngameBlock.classList.toggle("hidden", !ownIngame.checked);
    if (ownerOfflineBlock) ownerOfflineBlock.classList.toggle("hidden", !ownOffline.checked);
  }

  ownIngame?.addEventListener("change", toggleOwnerBlocks);
  ownOffline?.addEventListener("change", toggleOwnerBlocks);

  const playerIdField = document.getElementById("player-id");
  const cmdPownerField = document.getElementById("cmd-powner");
  const cmdPextdimField = document.getElementById("cmd-pextdim");
  const cmdPsetfactionField = document.getElementById("cmd-psetfaction");

  const charNameField = document.getElementById("char-name");
  const cmdGciofflineField = document.getElementById("cmd-gcioffline");

  charNameField?.addEventListener("input", () => {
    const name = charNameField.value.trim();
    cmdGciofflineField.value = name ? `/gcioffline ${name}` : "/gcioffline";
  });

  const ucpIdField = document.getElementById("ucp-id");
  const cmdPownerofflineField = document.getElementById("cmd-powneroffline");

  function updateOwnerCommands() {
    const propId = propertyIdField?.value.trim() || "";
    const playerId = playerIdField?.value.trim() || "";
    const characterId = ucpIdField?.value.trim() || "";

    let powner = "/powner";
    if (propId) powner += ` ${propId}`;
    powner += " 1";
    if (playerId) powner += ` ${playerId}`;
    if (cmdPownerField) cmdPownerField.value = powner;

    let powneroffline = "/powneroffline";
    if (propId) powneroffline += ` ${propId}`;
    powneroffline += " 1";
    if (characterId) powneroffline += ` ${characterId}`;
    if (cmdPownerofflineField) cmdPownerofflineField.value = powneroffline;
  }

  function updateStage3Commands() {
    const propId = propertyIdField?.value.trim() || "";

    if (cmdPextdimField) {
      cmdPextdimField.value = propId ? `/pextdim ${propId}` : "/pextdim";
    }

    if (cmdPsetfactionField) {
      cmdPsetfactionField.value = propId
        ? `/psetfaction ${propId} 770 confirm`
        : "/psetfaction 770 confirm";
    }
  }

  playerIdField?.addEventListener("input", updateOwnerCommands);
  ucpIdField?.addEventListener("input", updateOwnerCommands);
  propertyIdField?.addEventListener("input", () => {
    updateSetdimCommand();
    updateOwnerCommands();
    updateStage3Commands();
  });

  document.querySelectorAll(".btn-copy").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.copyFrom;
      const input = document.getElementById(id);
      if (!input) return;

      copyText(input.value);

      const old = btn.textContent;
      btn.textContent = "Copied";
      setTimeout(() => {
        btn.textContent = old;
      }, 900);
    });
  });

  function collectEventFromForm() {
    const handler = (document.getElementById("handler")?.value || "").trim();
    const type = document.getElementById("event-type")?.value || "";
    const date = document.getElementById("event-date")?.value || "";
    const time = document.getElementById("event-time")?.value || "";
    const name = (document.getElementById("event-name")?.value || "").trim();
    const district = (document.getElementById("event-district")?.value || "").trim();
    const discord = (document.getElementById("discord-link")?.value || "").trim();
    const description = (document.getElementById("event-description")?.value || "").trim();
    const propertyId = (document.getElementById("property-id")?.value || "").trim();
    const notes = (document.getElementById("notes")?.value || "").trim();

    const base = {
      handler,
      type,
      date,
      time,
      name,
      district,
      discord,
      description,
      propertyId,
      notes,
    };

    if (editingEventId) {
      const existing = events.find((e) => String(e.id) === String(editingEventId));
      return {
        ...base,
        id: editingEventId,
        createdAt: existing?.createdAt || new Date().toISOString().slice(0, 19).replace("T", " "),
        status: existing?.status || "NEW",
      };
    }

    return {
      ...base,
      status: "NEW",
      createdAt: new Date().toISOString().slice(0, 19).replace("T", " "),
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
    document.getElementById("event-description").value = ev.description || "";
    document.getElementById("property-id").value = ev.propertyId || "";
    document.getElementById("notes").value = ev.notes || "";

    clearErrors();
    updateCreateCommand();
    updateSetdimCommand();
    updateOwnerCommands();
    updateStage3Commands();
  }

  const params = new URLSearchParams(window.location.search);
  const editId = params.get("id");

  function resetForm() {
    document
      .querySelectorAll("input[type='text'], input[type='date'], input[type='time'], textarea")
      .forEach((el) => {
        if (el.id.startsWith("cmd-")) return;
        el.value = "";
      });

    const typeSelect = document.getElementById("event-type");
    if (typeSelect) typeSelect.value = "";

    if (ownIngame) ownIngame.checked = true;
    if (ownOffline) ownOffline.checked = false;

    toggleOwnerBlocks();
    clearErrors();
    editingEventId = null;

    applyStoredHandlerToField();
    updateCreateCommand();
    updateSetdimCommand();
    updateOwnerCommands();
    updateStage3Commands();
    setStage(1);
  }

  btnBack.addEventListener("click", () => {
    if (currentStage > 1) setStage(currentStage - 1);
  });

  btnNext.addEventListener("click", async () => {
    if (currentStage === 1) {
      if (!validateStage1()) return;
      setStage(2);
      return;
    }

    if (currentStage === 2) {
      setStage(3);
      return;
    }

    if (!validateBeforeSubmit()) return;

    try {
      const formEvent = collectEventFromForm();
      await saveEvent(formEvent);
      await loadEvents();
      await setStoredHandler((document.getElementById("handler")?.value || "").trim());
      alert("Event saved. You will see it on the Events page.");
      resetForm();
    } catch (error) {
      alert(`Could not save event: ${error.message}`);
    }
  });

  btnCancel.addEventListener("click", resetForm);

  stagePills.forEach((pill) => {
    pill.addEventListener("click", () => {
      const n = Number(pill.dataset.stageLabel);
      if (n <= currentStage) setStage(n);
    });
  });

  try {
    await Promise.all([loadEvents(), loadSettings()]);

    if (editId) {
      const existing = events.find((e) => String(e.id) === String(editId));
      if (existing) {
        editingEventId = existing.id;
        loadEventIntoForm(existing);
      }
    }
  } catch (error) {
    alert(`Could not load event data: ${error.message}`);
  }

  applyStoredHandlerToField();
  updateCreateCommand();
  updateSetdimCommand();
  updateOwnerCommands();
  updateStage3Commands();
  toggleOwnerBlocks();
  setStage(1);
});
