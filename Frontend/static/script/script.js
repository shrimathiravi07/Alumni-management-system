function isStrongPassword(password) {
  const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
  return regex.test(password);
}

document.getElementById('registerForm')?.addEventListener('submit', function (e) {
  e.preventDefault();
  const pass = document.getElementById('regPassword').value;
  const confirm = document.querySelectorAll('input[type="password"]')[1].value;
  if (!isStrongPassword(pass)) {
      alert("Password must include uppercase, lowercase, number, special char, and be 8+ characters.");
      return;
  }
  if (pass !== confirm) {
      alert("Passwords do not match!");
      return;
  }
  alert("Registration successful!");
});

function selectRole(role) {
  document.getElementById('userRole').value = role;
  document.querySelectorAll('.role-buttons button').forEach(btn => btn.classList.remove('selected'));
  event.target.classList.add('selected');
}

function forgotPassword() {
  const number = prompt("Enter your mobile number to receive OTP:");
  if (number && number.length === 10) {
      alert("OTP sent to " + number + ". (This is a demo alert.)");
  } else {
      alert("Please enter a valid 10-digit mobile number.");
  }
}

// Student FAQ submission
document.getElementById('studentFaqForm')?.addEventListener('submit', function(e) {
  e.preventDefault();
  const question = document.getElementById('studentQuestion').value.trim();

  if (!question) {
      alert('Question cannot be empty.');
      return;
  }

  if (question.length > 255) {
      alert('Question must be 255 characters or less.');
      return;
  }

  fetch('submit_faq.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ question })
  })
  .then(response => {
      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
      return response.json();
  })
  .then(data => {
      alert(data.message);
      if (data.status === 'success') {
          document.getElementById('studentFaqForm').reset();
      }
  })
  .catch(error => {
      console.error('Error submitting question:', error);
      alert('Error submitting question: ' + error.message);
  });
});

// Load pending FAQs for alumni
function loadPendingFAQs() {
  const container = document.getElementById('pendingFaqContainer');
  if (!container) return;

  fetch('faq.php?status=pending')
      .then(response => {
          if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
          return response.json();
      })
      .then(data => {
          container.innerHTML = data.map(faq => `
              <div class="faq-item">
                  <h3>${faq.question}</h3>
                  <form class="answer-faq-form" data-faq-id="${faq.id}">
                      <input type="text" name="answer" placeholder="Enter your answer" required />
                      <button type="submit">Submit Answer</button>
                  </form>
              </div>
          `).join('');

          // Attach event listeners to answer forms
          document.querySelectorAll('.answer-faq-form').forEach(form => {
              form.addEventListener('submit', function(e) {
                  e.preventDefault();
                  const faqId = this.getAttribute('data-faq-id');
                  const answer = this.querySelector('input[name="answer"]').value.trim();

                  if (!answer) {
                      alert('Answer cannot be empty.');
                      return;
                  }

                  fetch('answer_faq.php', {
                      method: 'POST',
                      headers: { 'Content-Type': 'application/json' },
                      body: JSON.stringify({ faq_id: faqId, answer })
                  })
                  .then(response => {
                      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                      return response.json();
                  })
                  .then(data => {
                      alert(data.message);
                      if (data.status === 'success') {
                          loadPendingFAQs(); // Refresh pending FAQs
                      }
                  })
                  .catch(error => {
                      console.error('Error submitting answer:', error);
                      alert('Error submitting answer: ' + error.message);
                  });
              });
          });
      })
      .catch(error => {
          console.error('Error fetching pending FAQs:', error);
          container.innerHTML = '<p>Failed to load FAQs.</p>';
      });
}

// Load FAQs on page load for FAQs page
function loadFAQs() {
  const faqContainer = document.getElementById('faqContainer');
  if (!faqContainer) return;

  fetch('faq.php')
      .then(response => {
          if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
          return response.json();
      })
      .then(data => {
          faqContainer.innerHTML = data.map(faq => `
              <div class="faq-item ${faq.status === 'pending' ? 'pending' : ''}">
                  <h3>${faq.question}</h3>
                  <p>${faq.answer || 'This question is pending an answer.'}</p>
              </div>
          `).join('');
      })
      .catch(error => {
          console.error('Error fetching FAQs:', error);
          alert('Failed to load FAQs: ' + error.message);
      });
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
  loadFAQs();
  loadPendingFAQs();
});