document.addEventListener("DOMContentLoaded", async () => {
  const API_EVENTS = "api/environmental-events.php";

  const form = document.getElementById("env-form");
  const formTitle = document.getElementById("env-form-title");
  const cancelBtn = document.getElementById("env-cancel-btn");
  const submitBtn = document.getElementById("env-submit-btn");

  if (!form || !formTitle || !cancelBtn || !submitBtn) return;

  const fields = {
    type: document.getElementById("env-type"),
    name: document.getElementById("env-name"),
    district: document.getElementById("env-district"),
    eventId: document.getElementById("env-event-id"),
    factionFlags: document.getElementById("env-faction-flags"),
    weight: document.getElementById("env-weight"),
    label: document.getElementById("env-label"),
  };

  let events = [];
  let editingEventId = null;

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

  function getWeight(ev) {
    const n = Number(ev.weight);
    if (Number.isNaN(n)) return 5;
    return Math.max(1, Math.min(10, Math.round(n)));
  }

  function resetForm() {
    editingEventId = null;
    fields.type.value = "";
    fields.name.value = "";
    fields.district.value = "";
    fields.eventId.value = "";
    fields.factionFlags.value = "";
    fields.weight.value = "5";
    fields.label.value = "";
    formTitle.textContent = "Create Environmental Event";
    submitBtn.textContent = "Create Event";
  }

  function populateForm(ev) {
    editingEventId = ev.id;
    fields.type.value = ev.type || "";
    fields.name.value = ev.name || "";
    fields.district.value = ev.district || "";
    fields.eventId.value = ev.eventId || ev.event_id || "";
    fields.factionFlags.value = ev.factionFlags || ev.faction_flags || "";
    fields.weight.value = String(getWeight(ev));
    fields.label.value = ev.label || ev.description || "";
    formTitle.textContent = "Edit Environmental Event";
    submitBtn.textContent = "Save Changes";
  }

  function collectFormPayload() {
    const weight = Math.max(1, Math.min(10, Number(fields.weight.value || 5)));

    const payload = {
      type: fields.type.value.trim(),
      name: fields.name.value.trim(),
      district: fields.district.value.trim(),
      event_id: fields.eventId.value.trim(),
      faction_flags: fields.factionFlags.value.trim(),
      weight,
      label: fields.label.value.trim(),
    };

    if (editingEventId) {
      const existing = events.find((e) => String(e.id) === String(editingEventId));
      payload.id = editingEventId;
      payload.createdAt = existing?.createdAt || new Date().toISOString().slice(0, 19).replace("T", " ");
      payload.banner_url = existing?.bannerUrl || existing?.banner_url || "";
      payload.banner_pos_x = Number(existing?.bannerPosX ?? existing?.banner_pos_x ?? 50);
      payload.banner_pos_y = Number(existing?.bannerPosY ?? existing?.banner_pos_y ?? 50);
      payload.banner_zoom = Number(existing?.bannerZoom ?? existing?.banner_zoom ?? 1);
    } else {
      payload.createdAt = new Date().toISOString().slice(0, 19).replace("T", " ");
    }

    return payload;
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (!fields.name.value.trim()) {
      alert("Name is required.");
      return;
    }

    const weight = Number(fields.weight.value || 0);
    if (Number.isNaN(weight) || weight < 1 || weight > 10) {
      alert("Weight must be between 1 and 10.");
      return;
    }

    try {
      const payload = collectFormPayload();
      await saveEvent(payload);
      window.location.href = "environmental-events.html";
    } catch (error) {
      alert(`Could not save event: ${error.message}`);
    }
  });

  cancelBtn.addEventListener("click", () => {
    if (editingEventId) {
      window.location.href = "environmental-events.html";
      return;
    }
    resetForm();
  });

  try {
    await loadEvents();

    const params = new URLSearchParams(window.location.search);
    const editId = params.get("id");
    if (editId) {
      const existing = events.find((e) => String(e.id) === String(editId));
      if (existing) populateForm(existing);
    }
  } catch (error) {
    alert(`Could not load environmental event data: ${error.message}`);
  }
});
