
// ELEMENTS

const chatWindow = document.getElementById("chatWindow");
const chatForm = document.getElementById("chatForm");
const messageInput = document.getElementById("messageInput");
const loadHistoryBtn = document.getElementById("loadHistoryBtn");
const historyBox = document.getElementById("historyBox");
const csvForm = document.getElementById("csvForm");
const csvFile = document.getElementById("csvFile");
const csvResult = document.getElementById("csvResult");
const chartCanvas = document.getElementById("chartCanvas");
const voiceToggle = document.getElementById("voiceToggle");
const darkToggle = document.getElementById("darkToggle");
const sidebarToggle = document.getElementById("sidebarToggle");
const appEl = document.documentElement;

let speechOn = false;
let chartInstance = null;



// DARK MODE

if (localStorage.getItem("theme") === "dark") {
    appEl.classList.add("dark");
    darkToggle.checked = true;
}

darkToggle.addEventListener("change", () => {
    const isDark = darkToggle.checked;
    appEl.classList.toggle("dark", isDark);
    localStorage.setItem("theme", isDark ? "dark" : "light");
});



// VOICE TOGGLE

voiceToggle.addEventListener("change", () => {
    speechOn = voiceToggle.checked;
});



// SIDEBAR COLLAPSE / EXPAND

const sidebar = document.querySelector(".sidebar");
if (sidebarToggle) {
    sidebarToggle.addEventListener("click", () => {
        sidebar.classList.toggle("collapsed");
        document.querySelector(".app").classList.toggle("expand");
    });
}


// TYPING INDICATOR

function showTyping() {
    const indicator = document.createElement("div");
    indicator.className = "typing-indicator";
    indicator.innerHTML = `
        <span></span><span></span><span></span>
        <p>AI is thinking...</p>
    `;
    chatWindow.appendChild(indicator);
    chatWindow.scrollTop = chatWindow.scrollHeight;
    return indicator;
}


// APPEND USER / BOT MESSAGES

function appendMessage(sender, text) {
    const el = document.createElement("div");
    el.className = "msg " + (sender === "user" ? "user" : "bot");

    el.innerHTML = `
        <div class="meta">${sender === "user" ? "You" : "Bot"} • 
            ${new Date().toLocaleTimeString()}
        </div>
        <div class="content"></div>
    `;

    el.querySelector(".content").textContent = text;

    chatWindow.appendChild(el);
    chatWindow.scrollTop = chatWindow.scrollHeight;

    if (sender === "bot" && speechOn) speak(text);

    return el;
}



// TYPEWRITER EFFECT FOR AI

function animateBotMessage(element, fullText) {
    let idx = 0;

    function step() {
        element.innerHTML = escapeHtml(fullText.substring(0, idx)).replace(/\n/g, "<br>");
        idx++;

        chatWindow.scrollTop = chatWindow.scrollHeight;

        if (idx <= fullText.length) {
            requestAnimationFrame(step);
        }
    }
    requestAnimationFrame(step);
}


// ESCAPE HTML

function escapeHtml(unsafe) {
    return unsafe.replace(/[&<"'>]/g, m => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;"
    }[m]));
}


// CHAT SUBMIT

chatForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const text = messageInput.value.trim();
    if (!text) return;
    messageInput.value = "";

    appendMessage("user", text);

    const typingBubble = showTyping();

    try {
        const reply = await askOllama(text);
        typingBubble.remove();

        const botMsg = appendMessage("bot", "");
        const contentDiv = botMsg.querySelector(".content");

        animateBotMessage(contentDiv, reply);

        await saveMessage("user", text);
        await saveMessage("bot", reply);

    } catch (err) {
        typingBubble.remove();
        appendMessage("bot", "Error: " + err.message);
        console.error(err);
    }
});


// OLLAMA STREAMING

async function askOllama(prompt) {
    const response = await fetch("http://localhost:11434/api/generate", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ model: "llama3.2", prompt })
    });

    const reader = response.body.getReader();
    let aiText = "";

    while (true) {
        const { value, done } = await reader.read();
        if (done) break;

        const chunk = new TextDecoder().decode(value).trim();
        const lines = chunk.split("\n");

        for (const line of lines) {
            if (!line) continue;
            const data = JSON.parse(line);
            if (data.response) aiText += data.response;
        }
    }
    return aiText || "(No response)";
}


// SAVE MESSAGE TO DB

async function saveMessage(sender, message) {
    await fetch("save_message.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ sender, message })
    });
}


// LOAD HISTORY

loadHistoryBtn?.addEventListener("click", async () => {
    historyBox.innerHTML = "Loading...";

    try {
        const res = await fetch("load_history.php");
        const data = await res.json();

        historyBox.innerHTML = "";
        data.forEach(row => {
            const d = document.createElement("div");
            d.innerHTML = `
                <strong>${row.sender}</strong>: 
                ${escapeHtml(row.message)}
                <small style="color:#999">(${row.created_at})</small>
            `;
            historyBox.appendChild(d);
        });
    } catch (e) {
        historyBox.innerText = "Could not load.";
        console.error(e);
    }
});


// CSV UPLOAD + CHART

csvForm?.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (!csvFile.files[0]) {
        csvResult.textContent = "Select a CSV file.";
        return;
    }

    const fd = new FormData();
    fd.append("file", csvFile.files[0]);

    csvResult.textContent = "Uploading...";

    try {
        const res = await fetch("upload_csv.php", { method: "POST", body: fd });
        const data = await res.json();

        csvResult.innerHTML = `<pre>${escapeHtml(JSON.stringify(data.summary, null, 2))}</pre>`;
        if (data.chart) drawChart(data.chart);

    } catch (err) {
        csvResult.textContent = "Upload error";
        console.error(err);
    }
});

function drawChart(values) {
    if (chartInstance) chartInstance.destroy();

    const labels = values.map((_, i) => i + 1);

    chartInstance = new Chart(chartCanvas, {
        type: "line",
        data: {
            labels,
            datasets: [
                { data: values, fill: false, borderWidth: 2, tension: 0.3 }
            ]
        },
        options: { plugins: { legend: { display: false } } }
    });
}



// SPEECH

function speak(text) {
    if (!("speechSynthesis" in window)) return;
    const s = new SpeechSynthesisUtterance(text);
    s.rate = 1;
    speechSynthesis.cancel();
    speechSynthesis.speak(s);
}
