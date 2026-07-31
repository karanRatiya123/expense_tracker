// profile.js — minimal client niceties for the profile page.
// Forms already POST server-side; this only adds light validation + UX.

(function () {
  const pwForm = document.getElementById('pwForm');
  if (pwForm) {
    pwForm.addEventListener('submit', (e) => {
      const n = pwForm.querySelector('#new_password').value;
      const c = pwForm.querySelector('#confirm_password').value;
      if (n.length < 6) { e.preventDefault(); alert('New password must be at least 6 characters.'); return; }
      if (n !== c)      { e.preventDefault(); alert('Passwords do not match.'); return; }
    });
  }

  const infoForm = document.getElementById('infoForm');
  if (infoForm) {
    infoForm.addEventListener('submit', (e) => {
      const name = infoForm.querySelector('#name').value.trim();
      const email = infoForm.querySelector('#email').value.trim();
      if (name.length < 2) { e.preventDefault(); alert('Name is too short.'); return; }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { e.preventDefault(); alert('That email looks off.'); return; }
    });
  }
})();
