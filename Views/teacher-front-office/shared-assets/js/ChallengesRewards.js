function handleChallenges() { 
  const teacherID = 1;
  if (DB_AVAILABLE) {
    fetch('../../Controllers/ChallengesController.php?action=all').then(res => res.json()).then(data => {
      const challenges = data.challenges.filter(c => c.createdBy == teacherID);
      renderChallengesTable(challenges);
    }).catch(() => renderChallengesTable([])); // Enhancement: Error handling
  } else {
    let challenges = JSON.parse(localStorage.getItem('challenges') || '[]');
    renderChallengesTable(challenges);
  }
}

function handleRewards() {
  if (DB_AVAILABLE) {
    fetch('../../Controllers/RewardsController.php?action=getAll').then(res => res.json()).then(data => {
      renderRewardsTable(data.rewards);
    }).catch(() => renderRewardsTable([])); // Enhancement: Error handling
  } else {
    let rewards = JSON.parse(localStorage.getItem('rewards') || '[]');
    renderRewardsTable(rewards);
  }
}

function renderChallengesTable(challenges) {
  const table = '<table class="table"><thead><tr><th>Title</th><th>Type</th><th>Points</th><th>Status</th><th>Actions</th></tr></thead><tbody>' +
    challenges.map(c => `<tr><td>${c.title}</td><td><span class="badge badge-${c.type.toLowerCase()}">${c.type}</span></td><td>${c.points}</td><td>${c.status}</td><td><button class="btn btn-sm btn-warning edit-btn" data-id="${c.id}">✏️ Edit</button> <button class="btn btn-sm btn-danger delete-btn" data-id="${c.id}">🗑️ Delete</button></td></tr>`).join('') +
    '</tbody></table>';
  document.getElementById('challengeTable').innerHTML = table;
}

function renderRewardsTable(rewards) {
  const table = '<table class="table"><thead><tr><th>Title</th><th>Type</th><th>Cost</th><th>Availability</th><th>Actions</th></tr></thead><tbody>' +
    rewards.map(r => `<tr><td>${r.title}</td><td>${r.type}</td><td>${r.pointsCost}</td><td>${r.availability}</td><td><button class="btn btn-sm btn-warning edit-btn" data-id="${r.id}">✏️ Edit</button> <button class="btn btn-sm btn-danger delete-btn" data-id="${r.id}">🗑️ Delete</button></td></tr>`).join('') +
    '</tbody></table>';
  document.getElementById('rewardTable').innerHTML = table;
}

document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('challengeFormData');
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const data = {
        title: document.getElementById('title').value,
        description: document.getElementById('description').value,
        type: document.getElementById('type').value,
        points: parseInt(document.getElementById('pointsAward').value),
        criteria: document.getElementById('criteria').value,
        status: document.getElementById('status').value,
        createdBy: 1
      };
      if (DB_AVAILABLE) {
        fetch('../../Controllers/ChallengesController.php?action=create', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) }).then(() => handleChallenges()).catch(() => alert('Error creating challenge.'));
      } else {
        createChallenge(data);
        handleChallenges();
      }
    });
  }
});