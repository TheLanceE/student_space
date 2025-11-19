(function(){
  const params = new URLSearchParams(location.search);
  const quizId = params.get('quizId');
  const quiz = Data.getQuizById(quizId);
  const user = Storage.get('currentUser');

  if(!quiz){
    document.getElementById('quizTitle').textContent = 'Quiz not found';
    document.getElementById('quizForm').style.display = 'none';
    return;
  }

  let remaining = quiz.durationSec || 60;
  let intervalId = null;

  function startTimer(){
    const timeLeft = document.getElementById('timeLeft');
    timeLeft.textContent = remaining;
    intervalId = setInterval(()=>{
      remaining -= 1;
      timeLeft.textContent = remaining;
      if(remaining <= 0){
        clearInterval(intervalId);
        submitQuiz();
      }
    }, 1000);
  }

  function renderQuiz(){
    document.getElementById('quizTitle').textContent = quiz.title;
    const container = document.getElementById('questions');
    container.innerHTML = '';
    quiz.questions.forEach((q, idx)=>{
      const block = document.createElement('div');
      block.className = 'mb-4 border-bottom pb-3';
      block.innerHTML = `
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div><strong>Q${idx+1}.</strong> ${q.text}</div>
          <button type="button" class="btn btn-sm btn-outline-warning report-btn" data-qid="${q.id}" data-qtext="${q.text}">
            <small>Report Issue</small>
          </button>
        </div>
        ${q.options.map((opt, i)=> `
          <div class="form-check">
            <input class="form-check-input" type="radio" name="${q.id}" id="${q.id}_${i}" value="${i}">
            <label class="form-check-label" for="${q.id}_${i}">${opt}</label>
          </div>
        `).join('')}
        <div id="feedback_${q.id}" class="mt-2 small"></div>
      `;
      container.appendChild(block);
    });

    // Bind report buttons
    document.querySelectorAll('.report-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const qid = btn.getAttribute('data-qid');
        const qtext = btn.getAttribute('data-qtext');
        document.getElementById('reportQuizId').value = quizId;
        document.getElementById('reportQuestionId').value = qid;
        const modal = new bootstrap.Modal(document.getElementById('reportModal'));
        modal.show();
      });
    });
  }

  function submitQuiz(){
    if(intervalId) clearInterval(intervalId);
    document.getElementById('submitBtn').disabled = true;

    let correct = 0;
    const details = [];
    for(const q of quiz.questions){
      const selected = document.querySelector(`input[name="${q.id}"]:checked`);
      const chosenIdx = selected ? parseInt(selected.value, 10) : -1;
      const isCorrect = chosenIdx === q.correctIndex;
      if(isCorrect) correct += 1;
      details.push({ qid: q.id, chosenIdx, correctIdx: q.correctIndex, isCorrect });

      const fb = document.getElementById(`feedback_${q.id}`);
      fb.className = 'mt-2 small ' + (isCorrect ? 'quiz-correct p-2' : 'quiz-incorrect p-2');
      fb.textContent = isCorrect ? 'Correct!' : `Incorrect. Correct answer: ${q.options[q.correctIndex]}`;
    }

    const existingAttempts = (window.Data && typeof Data.getScoresForUser === 'function')
      ? Data.getScoresForUser(user.id).filter(s => s.quizId === quiz.id && s.type === 'quiz')
      : [];

    const record = {
      id: Database.nextId('score'),
      userId: user.id,
      username: user.username,
      courseId: quiz.courseId,
      quizId: quiz.id,
      score: correct,
      total: quiz.questions.length,
      durationSec: (quiz.durationSec || 60) - remaining,
      attempt: existingAttempts.length + 1,
      timestamp: new Date().toISOString(),
      type: 'quiz'
    };

    if(window.Data && typeof Data.saveScore === 'function'){
      Data.saveScore(record);
    }

    // Result panel
    const pct = Math.round((record.score / record.total) * 100);
    const panel = document.getElementById('resultPanel');
    panel.style.display = 'block';
    panel.innerHTML = `
      <div class="card shadow-sm">
        <div class="card-body">
          <h2 class="h5 mb-2">Your Result</h2>
          <p class="mb-2"><strong>${pct}%</strong> (${record.score}/${record.total}) in ${record.durationSec}s · Attempt #${record.attempt}</p>
          <div class="mb-2">
            <strong>Suggestions:</strong>
            <ul id="resultSuggestions" class="mt-2"></ul>
          </div>
          <div class="d-flex gap-2">
            <a href="courses.php" class="btn btn-outline-secondary">Back to Courses</a>
            <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
          </div>
        </div>
      </div>
    `;
    const suggestions = SuggestionEngine.generate(user);
    const ul = panel.querySelector('#resultSuggestions');
    suggestions.forEach(s => { const li = document.createElement('li'); li.textContent = s; ul.appendChild(li); });
  }

  document.getElementById('quizForm').addEventListener('submit', function(e){ e.preventDefault(); submitQuiz(); });

  // Handle report submission
  document.getElementById('submitReport').addEventListener('click', () => {
    const form = document.getElementById('reportForm');
    const type = document.getElementById('reportType').value;
    const desc = document.getElementById('reportDescription').value.trim();
    
    if(!type || !desc) {
      alert('Please fill in all fields');
      return;
    }

    const report = {
      id: Database.nextId('qreport'),
      quizId: document.getElementById('reportQuizId').value,
      questionId: document.getElementById('reportQuestionId').value,
      reportedBy: user.id,
      reportType: type,
      description: desc,
      status: 'pending',
      createdAt: new Date().toISOString()
    };

    Database.insert('quizReports', report);
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
    modal.hide();
    form.reset();
    alert('Thank you! Your report has been submitted and will be reviewed by teachers and administrators.');
  });

  renderQuiz();
  startTimer();
})();