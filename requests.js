// Fetch requests from API
async function fetchRequests() {
  try {
    const res = await fetch('api/get_requests.php');
    const data = await res.json();

    const container = document.getElementById('requestsContainer');
    container.innerHTML = '';

    if(data.length === 0){
      container.innerHTML = '<p>No requests found.</p>';
      return;
    }

    data.forEach(request => {
      const card = document.createElement('div');
      card.classList.add('card');
      card.innerHTML = `
        <h2>${request.name}</h2>
        <p><strong>Email:</strong> ${request.email}</p>
        <p><strong>Phone:</strong> ${request.phone}</p>
        <div class="message-box"><strong>Message:</strong><p>${request.message}</p></div>
        <p class="timestamp">${request.submitted_at}</p>
      `;

      // Show modal on click
      card.addEventListener('click', () => showModal(request));
      container.appendChild(card);
    });

  } catch(err){
    console.error(err);
    document.getElementById('requestsContainer').innerHTML = '<p>Error loading requests.</p>';
  }
}

// Modal logic
const modal = document.getElementById('modal');
const modalClose = document.getElementById('modalClose');

function showModal(request){
  document.getElementById('modalName').innerText = request.name;
  document.getElementById('modalEmail').innerText = request.email;
  document.getElementById('modalPhone').innerText = request.phone;
  document.getElementById('modalMessage').innerText = request.message;
  document.getElementById('modalTimestamp').innerText = request.submitted_at;

  modal.style.display = 'flex';
}

modalClose.addEventListener('click', () => modal.style.display = 'none');

// Close modal on outside click
window.addEventListener('click', e => {
  if(e.target === modal){
    modal.style.display = 'none';
  }
});

fetchRequests();
