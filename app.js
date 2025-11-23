const API_TASKS = "api/tasks.php";
const API_NOTES = "api/notes.php";

/* ------------------- SWITCH TABS ------------------- */
function showTab(tab) {
  document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
  document.querySelectorAll(".tabs button").forEach(b => b.classList.remove("active"));

  document.getElementById(tab).classList.add("active");
  document.querySelector(`.tabs button[data-tab="${tab}"]`).classList.add("active");
}

/* ------------------- LOAD TASKS ------------------- */
async function loadTasks() {
  let res = await fetch(API_TASKS);
  let tasks = await res.json();

  let list = document.getElementById("taskList");
  list.innerHTML = "";

  // ---------- PROGRESS ----------
  let completed = tasks.filter(t => t.status === "completed").length;
  let total = tasks.length;
  let percent = total === 0 ? 0 : Math.round((completed / total) * 100);

  document.getElementById("progressBar").style.width = percent + "%";
  document.getElementById("progressText").textContent = percent + "% completed";

  // ---------- RENDER TASKS ----------
  tasks.forEach(t => {
    let li = document.createElement("li");

    let due = t.due_date ? new Date(t.due_date) : null;
    let overdue = due && due < new Date() && t.status !== "completed";

    li.className =
      (overdue ? "overdue " : "") +
      (t.status === "completed" ? "completed" : "");

    li.innerHTML = `
      <span>${t.title} (${t.category || "General"})
      ${due ? " – due " + t.due_date : ""}</span>
      <div>
        <button onclick="toggleTask(${t.id}, '${t.status}')">✔</button>
        <button onclick="deleteTask(${t.id})">🗑</button>
      </div>
    `;
    list.appendChild(li);
  });
}

/* ------------------- ADD TASK ------------------- */
document.getElementById("taskForm").addEventListener("submit", async e => {
  e.preventDefault();

  let data = {
    title: document.getElementById("taskTitle").value,
    due_date: document.getElementById("taskDue").value,
    category: document.getElementById("taskCategory").value || "General"
  };

  await fetch(API_TASKS, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data)
  });

  e.target.reset();
  loadTasks();
});

/* ------------------- TOGGLE TASK ------------------- */
async function toggleTask(id, status) {
  await fetch(API_TASKS + "?id=" + id, {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      status: status === "completed" ? "pending" : "completed"
    })
  });

  loadTasks();
}

/* ------------------- DELETE TASK ------------------- */
async function deleteTask(id) {
  await fetch(API_TASKS + "?id=" + id, { method: "DELETE" });
  loadTasks();
}

/* ------------------- SEARCH TASKS ------------------- */
function filterTasks() {
  let q = document.getElementById("taskSearch").value.toLowerCase();

  document.querySelectorAll("#taskList li").forEach(li => {
    li.style.display = li.textContent.toLowerCase().includes(q) ? "" : "none";
  });
}

/* ------------------- CATEGORy FILTER (FIXED) ------------------- */
function filterByCategory() {
  let filter = document.getElementById("categoryFilter").value.toLowerCase();

  document.querySelectorAll("#taskList li").forEach(li => {
    // Extract category from "(Category)"
    let text = li.querySelector("span").textContent;
    let match = text.match(/\((.*?)\)/);
    let category = match ? match[1].toLowerCase() : "general";

    li.style.display =
      filter === "all" || category === filter
        ? ""
        : "none";
  });
}

/* ------------------- LOAD NOTES ------------------- */
async function loadNotes() {
  let res = await fetch(API_NOTES);
  let notes = await res.json();

  let list = document.getElementById("noteList");
  list.innerHTML = "";

  notes.forEach(n => {
    let li = document.createElement("li");
    li.innerHTML = `
      <span><b>${n.title}</b> – ${n.content} <i>(${n.tags})</i></span>
      <div><button onclick="deleteNote(${n.id})">🗑</button></div>
    `;
    list.appendChild(li);
  });
}

/* ------------------- ADD NOTE ------------------- */
document.getElementById("noteForm").addEventListener("submit", async e => {
  e.preventDefault();

  let data = {
    title: document.getElementById("noteTitle").value,
    content: document.getElementById("noteContent").value,
    tags: document.getElementById("noteTags").value
  };

  await fetch(API_NOTES, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data)
  });

  e.target.reset();
  loadNotes();
});

/* ------------------- DELETE NOTE ------------------- */
async function deleteNote(id) {
  await fetch(API_NOTES + "?id=" + id, { method: "DELETE" });
  loadNotes();
}

/* ------------------- SEARCH NOTES ------------------- */
function filterNotes() {
  let q = document.getElementById("noteSearch").value.toLowerCase();

  document.querySelectorAll("#noteList li").forEach(li => {
    li.style.display = li.textContent.toLowerCase().includes(q) ? "" : "none";
  });
}

/* ------------------- INIT ------------------- */
loadTasks();
loadNotes();

/* ------------------- THEME SWITCH ------------------- */
const themeBtn = document.getElementById("themeToggle");

themeBtn.addEventListener("click", () => {
  document.body.classList.toggle("dark");

  themeBtn.textContent =
    document.body.classList.contains("dark")
      ? "☀️ Light Mode"
      : "🌙 Dark Mode";
});
const showTasksBtn = document.getElementById("showTasksBtn");
const showNotesBtn = document.getElementById("showNotesBtn");
const taskFrame = document.getElementById("taskFrame");
const noteFrame = document.getElementById("noteFrame");

showTasksBtn.addEventListener("click", () => {
  taskFrame.classList.add("active");
  noteFrame.classList.remove("active");
  showTasksBtn.classList.add("active");
  showNotesBtn.classList.remove("active");
});

showNotesBtn.addEventListener("click", () => {
  noteFrame.classList.add("active");
  taskFrame.classList.remove("active");
  showNotesBtn.classList.add("active");
  showTasksBtn.classList.remove("active");
});
