<script>
var questionCount = 0;

document.addEventListener('DOMContentLoaded', function() {
    loadMyQuizzes();
    addQuestion();
});

function loadMyQuizzes() {
    fetch('/quizzes2/quizzes2/quiz/controller/quizcontroller.php?action=getMyQuizzes')
        .then(function(res) { return res.text(); })
        .then(function(htmlRows) {
            var container = document.getElementById('quizzesContainer');
            container.innerHTML = '<div class="card"><div class="card-body"><table class="table"><thead><tr><th>Title</th><th>Category</th><th>Grade</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead><tbody id="quizRows"></tbody></table></div></div>';
            var tbody = document.getElementById('quizRows');
            tbody.innerHTML = htmlRows;
        })
        .catch(function(err) {
            document.getElementById('quizzesContainer').innerHTML = '<div class="alert alert-danger">Error loading quizzes</div>';
            console.error('Error loading quizzes:', err);
        });
}

function addQuestion() {
    questionCount++;
    var html = '<div class="card mb-3 question-card" data-question="' + questionCount + '"><div class="card-body"><div class="d-flex justify-content-between align-items-center mb-3"><h6>Question ' + questionCount + '</h6><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeQuestion(' + questionCount + ')"><i class="bi bi-trash"></i></button></div><div class="mb-3"><label class="form-label">Question Text *</label><input type="text" class="form-control question-text"></div><div class="mb-2"><label class="form-label">Options *</label></div><div class="option-group"><div class="input-group mb-2"><span class="input-group-text">A</span><input type="text" class="form-control option-input" placeholder="Option A"><div class="input-group-text"><input class="form-check-input correct-option" type="radio" name="correct' + questionCount + '" value="0"></div></div><div class="input-group mb-2"><span class="input-group-text">B</span><input type="text" class="form-control option-input" placeholder="Option B"><div class="input-group-text"><input class="form-check-input correct-option" type="radio" name="correct' + questionCount + '" value="1"></div></div><div class="input-group mb-2"><span class="input-group-text">C</span><input type="text" class="form-control option-input" placeholder="Option C"><div class="input-group-text"><input class="form-check-input correct-option" type="radio" name="correct' + questionCount + '" value="2"></div></div><div class="input-group mb-2"><span class="input-group-text">D</span><input type="text" class="form-control option-input" placeholder="Option D"><div class="input-group-text"><input class="form-check-input correct-option" type="radio" name="correct' + questionCount + '" value="3"></div></div></div><small class="text-muted">Select the correct answer by clicking the radio button</small></div></div>';
    
    document.getElementById('questionsContainer').insertAdjacentHTML('beforeend', html);
}

function removeQuestion(num) {
    var el = document.querySelector('[data-question="' + num + '"]');
    if (el) el.remove();
}

// Delegated handler for Remove buttons to work for dynamically inserted questions
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-outline-danger');
    if (!btn) return;
    e.preventDefault();
    var card = btn.closest('.question-card');
    if (card) {
        card.remove();
    }
});

function buildFormBody(status) {
    var params = new URLSearchParams();
    params.set('title', document.getElementById('quizTitle').value);
    params.set('category', document.getElementById('quizCategory').value);
    params.set('gradeLevel', document.getElementById('quizGrade').value);
    params.set('description', document.getElementById('quizDescription').value);
    params.set('status', status);
    params.set('passing_grade', document.getElementById('passingGrade').value || 70);
    params.set('time_limit', document.getElementById('timeLimit').value || 0);
    
    var cards = document.querySelectorAll('.question-card');
    var qIndex = 0;
    for (var i = 0; i < cards.length; i++) {
        var card = cards[i];
        var qText = card.querySelector('.question-text').value;
        params.set('questions[' + qIndex + '][text]', qText);
        
        var options = card.querySelectorAll('.option-input');
        var correct = card.querySelector('.correct-option:checked');
        for (var j = 0; j < options.length; j++) {
            params.set('questions[' + qIndex + '][options][' + j + '][text]', options[j].value);
            var isC = correct && parseInt(correct.value) === j ? '1' : '0';
            params.set('questions[' + qIndex + '][options][' + j + '][is_correct]', isC);
        }
        qIndex++;
    }
    return params.toString();
}

function saveQuiz(status) {
    var params = new URLSearchParams(buildFormBody(status));
    params.set('ajax', '1');
    
    var form = document.getElementById('quizForm');
    var mode = form.getAttribute('data-mode');
    var quizId = form.getAttribute('data-quiz-id') || document.getElementById('quizId').value;
    
    var action = 'create';
    if (mode === 'edit' && quizId) {
        action = 'edit';
        params.set('quiz_id', quizId);
    }
    
    fetch('/quizzes2/quizzes2/quiz/controller/quizcontroller.php?action=' + action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(function(res) { return res.text(); })
    .then(function(text) {
        if (text.indexOf('quiz_id=') === 0) {
            var qid = text.split('=')[1];
            alert(action === 'edit' ? 'Quiz updated successfully' : 'Quiz created successfully');
            
            form.removeAttribute('data-mode');
            form.removeAttribute('data-quiz-id');
            document.getElementById('quizId').value = '';
            
            loadMyQuizzes();
            resetForm();
        } else {
            alert('Error saving quiz');
        }
    })
    .catch(function(err) {
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
    
    var formData = new FormData();
    formData.append('quiz_id', quizId);
    
    fetch('/quizzes2/quizzes2/quiz/controller/quizcontroller.php?action=delete', {
        method: 'POST',
        body: formData
    })
    .then(function(res) { return res.text(); })
    .then(function() {
        alert('Quiz deleted successfully');
        loadMyQuizzes();
    })
    .catch(function(err) {
        alert('Error deleting quiz');
        console.error('Delete error:', err);
    });
}

function editQuiz(quizId) {
    fetch('/quizzes2/quizzes2/quiz/controller/quizcontroller.php?action=getQuizFormData&id=' + quizId)
        .then(function(res) { return res.text(); })
        .then(function(data) {
            var params = new URLSearchParams(data);
            
            document.getElementById('quizId').value = params.get('quiz_id') || '';
            document.getElementById('quizTitle').value = params.get('title') || '';
            document.getElementById('quizCategory').value = params.get('category') || '';
            document.getElementById('quizGrade').value = params.get('gradeLevel') || '';
            document.getElementById('quizDescription').value = params.get('description') || '';
            document.getElementById('passingGrade').value = params.get('passing_grade') || '';
            document.getElementById('timeLimit').value = params.get('time_limit') || '';
            
            var status = params.get('status') || 'active';
            if (status === 'active') {
                document.getElementById('statusActive').checked = true;
            } else if (status === 'draft') {
                document.getElementById('statusDraft').checked = true;
            } else if (status === 'inactive') {
                document.getElementById('statusInactive').checked = true;
            }
            
            document.getElementById('questionsContainer').innerHTML = '';
            questionCount = 0;
            
            var index = 0;
            while (params.has('questions[' + index + '][text]')) {
                addQuestion();
                
                var questionCards = document.querySelectorAll('.question-card');
                var questionCard = questionCards[index];
                var questionText = questionCard.querySelector('.question-text');
                questionText.value = params.get('questions[' + index + '][text]') || '';
                
                var optionInputs = questionCard.querySelectorAll('.option-input');
                var correctOptions = questionCard.querySelectorAll('.correct-option');
                
                for (var j = 0; j < optionInputs.length; j++) {
                    optionInputs[j].value = params.get('questions[' + index + '][options][' + j + '][text]') || '';
                    
                    var isCorrect = params.get('questions[' + index + '][options][' + j + '][is_correct]') === '1';
                    if (isCorrect && correctOptions[j]) {
                        correctOptions[j].checked = true;
                    }
                }
                
                index++;
            }
            
            document.getElementById('quizForm').setAttribute('data-mode', 'edit');
            document.getElementById('quizForm').setAttribute('data-quiz-id', quizId);
            
            var createTab = document.querySelector('a[href="#create-quiz"]');
            if (createTab) createTab.click();
            
            alert('Quiz loaded for editing');
        })
        .catch(function(err) {
            alert('Error loading quiz for editing');
            console.error('Edit error:', err);
        });
}

function previewQuiz(quizId) {
    fetch('/quizzes2/quizzes2/quiz/controller/quizcontroller.php?action=preview&id=' + quizId)
        .then(function(res) { return res.text(); })
        .then(function(html) {
            document.getElementById('previewContent').innerHTML = html;
            var previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
            previewModal.show();
        })
        .catch(function(err) {
            alert('Error loading quiz preview');
            console.error('Preview error:', err);
        });
}
</script>
