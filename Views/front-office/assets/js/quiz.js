(() => {
  const ctx = window.__QUIZ_CONTEXT__ || {};
  const quiz = ctx.quiz;
  const studentId = ctx.studentId;
  const username = ctx.username;
  const csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

  if (!quiz || !quiz.id) {
    const title = document.getElementById('quizTitle');
    if (title) title.textContent = 'Quiz not found';
    const form = document.getElementById('quizForm');
    if (form) form.style.display = 'none';
    return;
  }

  let remaining = quiz.durationSec || 60;
  const startedAt = Date.now();
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

  async function submitQuiz(){
    if(intervalId) clearInterval(intervalId);
    document.getElementById('submitBtn').disabled = true;

    const answers = {};
    for(const q of quiz.questions){
      const selected = document.querySelector(`input[name="${q.id}"]:checked`);
      answers[q.id] = selected ? parseInt(selected.value, 10) : -1;
    }

    const durationSec = Math.max(0, Math.round((Date.now() - startedAt) / 1000));

    let apiResult;
    try {
      const res = await fetch('../../Controllers/ScoreController.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({
          action: 'submit_quiz_attempt',
          quizId: quiz.id,
          answers,
          durationSec,
          csrf_token: csrfToken
        })
      });
      apiResult = await res.json();
    } catch (e) {
      apiResult = { success: false, error: 'Network error while saving score' };
    }

    if (!apiResult || apiResult.success !== true) {
      alert((apiResult && apiResult.error) ? apiResult.error : 'Failed to submit quiz');
      document.getElementById('submitBtn').disabled = false;
      return;
    }

    const record = apiResult.record;
    const feedback = apiResult.feedback || {};

    // Per-question feedback
    for(const q of quiz.questions){
      const fb = document.getElementById(`feedback_${q.id}`);
      const f = feedback[q.id] || null;
      if (!fb || !f) continue;

      fb.className = 'mt-2 small ' + (f.isCorrect ? 'quiz-correct p-2' : 'quiz-incorrect p-2');
      const correctIdx = typeof f.correctIdx === 'number' ? f.correctIdx : -1;
      const correctText = (correctIdx >= 0 && correctIdx < q.options.length) ? q.options[correctIdx] : '';
      fb.textContent = f.isCorrect ? 'Correct!' : (correctText ? `Incorrect. Correct answer: ${correctText}` : 'Incorrect.');
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
    const ul = panel.querySelector('#resultSuggestions');

    const suggestions = [];
    if (pct >= 90) {
      suggestions.push('Great work — try a harder quiz next.');
    } else if (pct >= 70) {
      suggestions.push('Review the questions you missed and retry later.');
    } else {
      suggestions.push('Revisit the lesson content and practice similar problems.');
    }
    suggestions.forEach(s => { const li = document.createElement('li'); li.textContent = s; ul.appendChild(li); });
  }

  const quizForm = document.getElementById('quizForm');
  if (quizForm) {
    quizForm.addEventListener('submit', function(e){ e.preventDefault(); submitQuiz(); });
  }

  // Handle report submission
  document.getElementById('submitReport').addEventListener('click', async () => {
    const form = document.getElementById('reportForm');
    const type = document.getElementById('reportType').value;
    const desc = document.getElementById('reportDescription').value.trim();
    
    if(!type || !desc) {
      alert('Please fill in all fields');
      return;
    }

    let apiResult;
    try {
      const res = await fetch('../../Controllers/QuizReportController.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({
          action: 'submit_student_report',
          csrf_token: csrfToken,
          quizId: document.getElementById('reportQuizId').value,
          questionId: document.getElementById('reportQuestionId').value,
          reportType: type,
          description: desc
        })
      });
      apiResult = await res.json();
    } catch (e) {
      apiResult = { success: false, error: 'Network error while submitting report' };
    }

    if (!apiResult || apiResult.success !== true) {
      alert((apiResult && apiResult.error) ? apiResult.error : 'Failed to submit report');
      return;
    }
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
    modal.hide();
    form.reset();
    alert('Thank you! Your report has been submitted and will be reviewed by teachers and administrators.');
  });

  renderQuiz();
  startTimer();
})();