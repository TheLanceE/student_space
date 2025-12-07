<script>
let questionCount = 0;

document.addEventListener('DOMContentLoaded', function() {
    loadMyQuizzes();
    addQuestion();
});

function loadMyQuizzes() {
    fetch('/quizzes2/quizzes2/quiz/controller/quizcontroller.php?action=getMyQuizzes')
        .then(res => res.text())
        .then(htmlRows => {
            const container = document.getElementById('quizzesContainer');
            container.innerHTML = '<div class="card"><div class="card-body"><table class="table"><thead><tr><th>Title</th><th>Category</th><th>Grade</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead><tbody id="quizRows"></tbody></table></div></div>';
            const tbody = document.getElementById('quizRows');
            tbody.innerHTML = htmlRows;
        })
        .catch(err => {
            document.getElementById('quizzesContainer').innerHTML = '<div class="alert alert-danger">Error loading quizzes</div>';
            console.error('Error loading quizzes:', err);
        });
}

function addQuestion() {
    questionCount++;
    const html = `
    <div class="card mb-3 question-card" data-question="${questionCount}">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6>Question ${questionCount}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeQuestion(${questionCount})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="mb-3">
                <label class="form-label">Question Text *</label>
                <input type="text" class="form-control question-text">
            </div>
            <div class="mb-2">
                <label class="form-label">Options *</label>
            </div>
            <div class="option-group">
                <div class="input-group mb-2">
                    <span class="input-group-text">A</span>
                    <input type="text" class="form-control option-input" placeholder="Option A">
                    <div class="input-group-text">
                        <input class="form-check-input correct-option" type="radio" name="correct${questionCount}" value="0">
                    </div>
                </div>
                <div class="input-group mb-2">
                    <span class="input-group-text">B</span>
                    <input type="text" class="form-control option-input" placeholder="Option B">
                    <div class="input-group-text">
                        <input class="form-check-input correct-option" type="radio" name="correct${questionCount}" value="1">
                    </div>
                </div>
                <div class="input-group mb-2">
                    <span class="input-group-text">C</span>
                    <input type="text" class="form-control option-input" placeholder="Option C">
                    <div class="input-group-text">
                        <input class="form-check-input correct-option" type="radio" name="correct${questionCount}" value="2">
                    </div>
                </div>
                <div class="input-group mb-2">
                    <span class="input-group-text">D</span>
                    <input type="text" class="form-control option-input" placeholder="Option D">
                    <div class="input-group-text">
                        <input class="form-check-input correct-option" type="radio" name="correct${questionCount}" value="3">
                    </div>
                </div>
            </div>
            <small class="text-muted">Select the correct answer by clicking the radio button</small>
        </div>
    </div>
    `;
    
    document.getElementById('questionsContainer').insertAdjacentHTML('beforeend', html);
}

function removeQuestion(num) {
    document.querySelector(`[data-question="${num}"]`).remove();
}

function buildFormBody(status) {
    const params = new URLSearchParams();
    params.set('title', document.getElementById('quizTitle').value);
    params.set('category', document.getElementById('quizCategory').value);
    params.set('gradeLevel', document.getElementById('quizGrade').value);
    params.set('description', document.getElementById('quizDescription').value);
    params.set('status', status);
    params.set('passing_grade', document.getElementById('passingGrade').value || 70);
    params.set('time_limit', document.getElementById('timeLimit').value || 0);
    
    const cards = document.querySelectorAll('.question-card');
    let qIndex = 0;
    cards.forEach(card => {
        const qText = card.querySelector('.question-text').value;
        params.set(`questions[${qIndex}][text]`, qText);
        
        const options = card.querySelectorAll('.option-input');
        const correct = card.querySelector('.correct-option:checked');
        options.forEach((opt, i) => {
            params.set(`questions[${qIndex}][options][${i}][text]`, opt.value);
            const isC = correct && parseInt(correct.value) === i ? '1' : '0';
            params.set(`questions[${qIndex}][options][${i}][is_correct]`, isC);
        });
        qIndex++;
    });
    return params.toString();
}

function saveQuiz(status) {
    const params = new URLSearchParams(buildFormBody(status));
    params.set('ajax', '1');
    
    // Check if we're in edit mode
    const form = document.getElementById('quizForm');
    const mode = form.getAttribute('data-mode');
    const quizId = form.getAttribute('data-quiz-id') || document.getElementById('quizId').value;
    
    let action = 'create';
    if (mode === 'edit' && quizId) {
        action = 'edit';
        params.set('quiz_id', quizId);
    }
    
    fetch('/quizzes2/quizzes2/quiz/controller/quizcontroller.php?action=' + action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(res => res.text())
    .then(text => {
        if (text.indexOf('quiz_id=') === 0) {
            const qid = text.split('=')[1];
            alert(action === 'edit' ? 'Quiz updated successfully' : 'Quiz created successfully');
            
            // Reset form mode
            form.removeAttribute('data-mode');
            form.removeAttribute('data-quiz-id');
            document.getElementById('quizId').value = '';
            
            loadMyQuizzes();
            resetForm();
        } else {
            alert('Error saving quiz');
        }
    })
    .catch(err => {
        alert('Error saving quiz');
        console.error('Save error:', err);
    });
}

document.getElementById('quizForm').addEventListener('submit', function(e) {
    e.preventDefault();
    saveQuiz('active');
});

function saveDraft() {
    saveQuiz('draft');
}

function resetForm() {
    document.getElementById('quizForm').reset();
    document.getElementById('questionsContainer').innerHTML = '';
    questionCount = 0;
    addQuestion();
}

function deleteQuiz(quizId) {
    if (!confirm('Delete this quiz? This cannot be undone.')) return;
    
    const formData = new FormData();
    formData.append('quiz_id', quizId);
    
    fetch('/quizzes2/quizzes2/quiz/controller/quizcontroller.php?action=delete', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(() => {
        alert('Quiz deleted successfully');
        loadMyQuizzes();
    })
    .catch(err => {
        alert('Error deleting quiz');
        console.error('Delete error:', err);
    });
}

function editQuiz(quizId) {
    fetch('/quizzes2/quizzes2/quiz/controller/quizcontroller.php?action=getQuizFormData&id=' + quizId)
        .then(res => res.text())
        .then(data => {
            const params = new URLSearchParams(data);
            
            // Populate basic quiz information
            document.getElementById('quizId').value = params.get('quiz_id') || '';
            document.getElementById('quizTitle').value = params.get('title') || '';
            document.getElementById('quizCategory').value = params.get('category') || '';
            document.getElementById('quizGrade').value = params.get('gradeLevel') || '';
            document.getElementById('quizDescription').value = params.get('description') || '';
            document.getElementById('passingGrade').value = params.get('passing_grade') || '';
            document.getElementById('timeLimit').value = params.get('time_limit') || '';
            
            const status = params.get('status') || 'active';
            if (status === 'active') {
                document.getElementById('statusActive').checked = true;
            } else if (status === 'draft') {
                document.getElementById('statusDraft').checked = true;
            } else if (status === 'inactive') {
                document.getElementById('statusInactive').checked = true;
            }
            
            // Clear existing questions
            document.getElementById('questionsContainer').innerHTML = '';
            questionCount = 0;
            
            // Add questions from quiz data
            let index = 0;
            while (params.has('questions[' + index + '][text]')) {
                addQuestion();
                
                const questionCard = document.querySelectorAll('.question-card')[index];
                const questionText = questionCard.querySelector('.question-text');
                questionText.value = params.get('questions[' + index + '][text]') || '';
                
                const optionInputs = questionCard.querySelectorAll('.option-input');
                const correctOptions = questionCard.querySelectorAll('.correct-option');
                
                for (let j = 0; j < optionInputs.length; j++) {
                    optionInputs[j].value = params.get('questions[' + index + '][options][' + j + '][text]') || '';
                    
                    const isCorrect = params.get('questions[' + index + '][options][' + j + '][is_correct]') === '1';
                    if (isCorrect && correctOptions[j]) {
                        correctOptions[j].checked = true;
                    }
                }
                
                index++;
            }
            
            // Update form action to edit mode
            document.getElementById('quizForm').setAttribute('data-mode', 'edit');
            document.getElementById('quizForm').setAttribute('data-quiz-id', quizId);
            
            // Switch to create quiz tab
            const createTab = document.querySelector('a[href="#create-quiz"]');
            if (createTab) createTab.click();
            
            alert('Quiz loaded for editing');
        })
        .catch(err => {
            alert('Error loading quiz for editing');
            console.error('Edit error:', err);
        });
}

function previewQuiz(quizId) {
    fetch('/quizzes2/quizzes2/quiz/controller/quizcontroller.php?action=preview&id=' + quizId)
        .then(res => res.text())
        .then(html => {
            document.getElementById('previewContent').innerHTML = html;
            const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
            previewModal.show();
        })
        .catch(err => {
            alert('Error loading quiz preview');
            console.error('Preview error:', err);
        });
}
</script>
