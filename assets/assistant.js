// ====== VOICE ASSISTANT CONFIG ======
const synth = window.speechSynthesis;
let voiceEnabled = false; // stays off until user activates

// Optional: speech recognition setup
let recognition;
if ('webkitSpeechRecognition' in window) {
  recognition = new webkitSpeechRecognition();
  recognition.lang = 'en-US';
  recognition.continuous = false;
  recognition.interimResults = false;

  recognition.onresult = function (event) {
    const userSpeech = event.results[0][0].transcript.toLowerCase();
    appendMessage(userSpeech, 'user');
    processUserInput(userSpeech);
  };

  recognition.onerror = function (event) {
    appendMessage("Sorry, I couldn't hear you clearly.", 'bot');
  };
}

// ====== BUTTONS ======
const micBtn = document.createElement('button');
micBtn.textContent = '🎤';
micBtn.classList.add('btn');
micBtn.style.position = 'absolute';
micBtn.style.bottom = '15px';
micBtn.style.right = '10px';
micBtn.style.zIndex = '1000';
document.body.appendChild(micBtn);

// ====== EVENTS ======
micBtn.addEventListener('click', () => {
  if (recognition) {
    recognition.start();
    speak("Listening...");
  } else {
    alert('Voice recognition not supported on this browser.');
  }
});

// ====== HANDLE USER INPUT ======
async function processUserInput(userMessage) {
  const botReply = await getBotResponse(userMessage);
  appendMessage(botReply, 'bot');
  if (voiceEnabled) speak(botReply);
}

// ====== TOGGLE VOICE ======
const voiceToggle = document.createElement('button');
voiceToggle.textContent = '🔈 Voice OFF';
voiceToggle.classList.add('btn');
voiceToggle.style.position = 'absolute';
voiceToggle.style.bottom = '15px';
voiceToggle.style.right = '60px';
voiceToggle.style.zIndex = '1000';
document.body.appendChild(voiceToggle);

voiceToggle.addEventListener('click', () => {
  voiceEnabled = !voiceEnabled;
  voiceToggle.textContent = voiceEnabled ? '🔊 Voice ON' : '🔈 Voice OFF';
  showToast(`Voice ${voiceEnabled ? 'enabled' : 'disabled'}`);
});

// ====== SPEAK FUNCTION ======
function speak(text) {
  if (!synth || !voiceEnabled) return;
  const utterance = new SpeechSynthesisUtterance(text);
  utterance.lang = 'en-US';
  utterance.rate = 1;
  utterance.pitch = 1;
  synth.speak(utterance);
}
