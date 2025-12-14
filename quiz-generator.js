// quiz-generator.js
document.getElementById('quizForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const generateBtn = document.getElementById('generateBtn');
    const loading = document.getElementById('loading');
    const quizResults = document.getElementById('quizResults');
    const quizContainer = document.getElementById('quizContainer');
    
    // Show loading, hide results
    generateBtn.disabled = true;
    generateBtn.textContent = 'Generating...';
    loading.style.display = 'block';
    quizResults.style.display = 'none';
    quizContainer.innerHTML = '';
    
    // Get form values
    const quizData = {
        subject: document.getElementById('subject').value,
        topic: document.getElementById('topic').value,
        difficulty: document.getElementById('difficulty').value,
        num_questions: document.getElementById('numQuestions').value
    };
    
    try {
        const response = await fetch('generate_quiz.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(quizData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            displayQuiz(result.quiz);
            quizResults.style.display = 'block';
            showMessage('Quiz generated successfully!', 'success');
        } else {
            throw new Error(result.error || 'Failed to generate quiz');
        }
    } catch (error) {
        showMessage('Error: ' + error.message, 'error');
    } finally {
        generateBtn.disabled = false;
        generateBtn.textContent = 'Generate Quiz Questions';
        loading.style.display = 'none';
    }
});

function displayQuiz(quiz) {
    const container = document.getElementById('quizContainer');
    
    // Quiz header
    container.innerHTML = `
        <div style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #007bff;">
            <h3>${quiz.subject} - ${quiz.topic}</h3>
            <p>Total Questions: ${quiz.questions?.length || 0}</p>
        </div>
    `;
    
    // Display each question
    if (quiz.questions && Array.isArray(quiz.questions)) {
        quiz.questions.forEach((q, index) => {
            const questionHTML = `
                <div class="question-card" data-index="${index}">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                        <h4 style="margin:0;">Question ${index + 1}: ${q.question}</h4>
                        <button type="button" class="revoke-btn" onclick="revokeQuestion(this)" style="padding:6px 10px; border:1px solid #dc3545; color:#dc3545; background:#fff; border-radius:4px; cursor:pointer;">Remove</button>
                    </div>
                    <div style="margin-left: 20px;">
                        <p>A) ${q.options?.A || ''}</p>
                        <p>B) ${q.options?.B || ''}</p>
                        <p>C) ${q.options?.C || ''}</p>
                        <p>D) ${q.options?.D || ''}</p>
                    </div>
                    <p class="correct-answer">Correct Answer: ${q.correct_answer}</p>
                    ${q.explanation ? `<p class="explanation">💡 ${q.explanation}</p>` : ''}
                </div>
            `;
            container.innerHTML += questionHTML;
        });
    }
    
    // Add answer key
    container.innerHTML += `
        <div style="margin-top: 30px; padding: 15px; background: #e9f7fe; border-radius: 5px;">
            <h4>Answer Key</h4>
            <div id="answerKey"></div>
        </div>
    `;
    
    // Fill answer key
    const answerKey = document.getElementById('answerKey');
    quiz.questions?.forEach((q, index) => {
        answerKey.innerHTML += `<p>${index + 1}. ${q.correct_answer}</p>`;
    });
}

function exportQuiz() {
    const quizContainer = document.getElementById('quizContainer');
    const quizHTML = quizContainer.innerHTML;
    
    const blob = new Blob([`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Quiz Export</title>
            <style>
                body { font-family: Arial; padding: 20px; }
                .question-card { margin: 15px 0; padding: 10px; border: 1px solid #ccc; }
                .correct-answer { color: green; font-weight: bold; }
            </style>
        </head>
        <body>
            <h1>Generated Quiz</h1>
            ${quizHTML}
        </body>
        </html>
    `], { type: 'text/html' });
    
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'quiz-export.html';
    a.click();
    URL.revokeObjectURL(url);
}

function printQuiz() {
    const printContent = document.getElementById('quizContainer').innerHTML;
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = `
        <h1>Quiz Printout</h1>
        <div style="page-break-inside: avoid;">
            ${printContent}
        </div>
    `;
    
    window.print();
    document.body.innerHTML = originalContent;
    location.reload();
}

function showMessage(message, type) {
    const container = document.querySelector('.container');
    const existingMsg = document.querySelector('.message');
    if (existingMsg) existingMsg.remove();
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = message;
    
    container.insertBefore(messageDiv, container.firstChild);
    
    setTimeout(() => {
        messageDiv.remove();
    }, 5000);
}

// Remove a generated question card (revoke)
function revokeQuestion(indexOrBtn) {
    try {
        if (indexOrBtn && indexOrBtn.tagName) {
            // Called from button element
            var card = indexOrBtn.closest('.question-card');
            if (card && card.parentNode) {
                card.parentNode.removeChild(card);
            }
        } else {
            // Called with index number (legacy)
            var index = parseInt(indexOrBtn, 10);
            var cards = document.querySelectorAll('#quizContainer .question-card');
            for (var i = 0; i < cards.length; i++) {
                var idxAttr = cards[i].getAttribute('data-index');
                if (parseInt(idxAttr, 10) === index) {
                    cards[i].parentNode.removeChild(cards[i]);
                    break;
                }
            }
        }
        // Re-number remaining cards for clarity
        var renumCards = document.querySelectorAll('#quizContainer .question-card');
        for (var j = 0; j < renumCards.length; j++) {
            var h4 = renumCards[j].querySelector('h4');
            if (h4) {
                // Replace the leading "Question N:" label while keeping text
                var text = h4.textContent.replace(/^\s*Question\s*\d+:\s*/i, '');
                h4.textContent = 'Question ' + (j + 1) + ': ' + text;
            }
            // Update data-index for consistency with revoke
            renumCards[j].setAttribute('data-index', j);
        }
        showMessage('Question revoked.', 'success');
    } catch (e) {
        showMessage('Failed to revoke: ' + e.message, 'error');
    }
}

// Enable edit mode on generated question cards
function enableEditMode() {
    var cards = document.querySelectorAll('.question-card');
    for (var i = 0; i < cards.length; i++) {
        cards[i].contentEditable = true;
        cards[i].style.border = '2px dashed #007bff';
        cards[i].style.padding = '12px';
    }
    showMessage('Edit mode enabled. Click text to edit.', 'success');
}

// Save generated quiz into database via controller
async function saveQuizToDatabase() {
    try {
        var subjectEl = document.getElementById('subject');
        var topicEl = document.getElementById('topic');
        var difficultyEl = document.getElementById('difficulty');
        var title = (topicEl && topicEl.value ? topicEl.value : 'Generated') + ' Quiz';
        var category = subjectEl && subjectEl.value ? subjectEl.value : 'general';
        var gradeLevel = mapDifficultyToGrade(difficultyEl && difficultyEl.value ? difficultyEl.value : 'medium');
        var description = 'AI-generated quiz for ' + (subjectEl ? subjectEl.value : 'Subject') + ' - ' + (topicEl ? topicEl.value : 'Topic');
        var status = 'draft';
        var passing = '70';
        var timeLimit = '0';

        var payload = new URLSearchParams();
        payload.append('action', 'saveGeneratedQuiz');
        payload.append('title', title);
        payload.append('category', category);
        payload.append('gradeLevel', gradeLevel);
        payload.append('description', description);
        payload.append('status', status);
        payload.append('passing_grade', passing);
        payload.append('time_limit', timeLimit);

        var cards = document.querySelectorAll('#quizContainer .question-card');
        for (var i = 0; i < cards.length; i++) {
            var qText = '';
            var h4 = cards[i].querySelector('h4');
            if (h4 && h4.textContent) {
                qText = h4.textContent.replace(/^\s*Question\s*\d+:\s*/i, '').trim();
            }
            payload.append('questions[' + i + '][text]', qText);

            var optTexts = [];
            var pList = cards[i].querySelectorAll('div > p');
            for (var j = 0; j < pList.length; j++) {
                var txt = pList[j].textContent || '';
                txt = txt.replace(/^\s*[ABCD]\)\s*/, '').trim();
                optTexts.push(txt);
            }
            var correctEl = cards[i].querySelector('.correct-answer');
            var correctLabel = 'A';
            if (correctEl && correctEl.textContent) {
                var m = correctEl.textContent.match(/Correct\s*Answer:\s*([ABCD])/i);
                if (m && m[1]) { correctLabel = m[1].toUpperCase(); }
            }
            var correctIndex = { 'A': 0, 'B': 1, 'C': 2, 'D': 3 }[correctLabel];
            for (var k = 0; k < 4 && k < optTexts.length; k++) {
                payload.append('questions[' + i + '][options][' + k + '][text]', optTexts[k]);
                payload.append('questions[' + i + '][options][' + k + '][is_correct]', (k === correctIndex ? '1' : '0'));
            }
        }

        var base = (window.location.pathname.indexOf('/view/') !== -1) ? '../controller/quizcontroller.php' : 'controller/quizcontroller.php';
        var res = await fetch(base, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: payload.toString()
        });
        var data = await res.json();
        if (data && data.success) {
            showMessage('Quiz saved. ID: ' + data.quiz_id, 'success');
        } else {
            showMessage('Failed to save: ' + (data && data.error ? data.error : 'Unknown error'), 'error');
        }
    } catch (e) {
        showMessage('Error: ' + e.message, 'error');
    }
}

function mapDifficultyToGrade(diff) {
    var map = { 'easy': '5', 'medium': '8', 'hard': '11' };
    return map[diff] || '8';
}

// Expose helpers
window.enableEditMode = enableEditMode;
window.saveQuizToDatabase = saveQuizToDatabase;

// Delegate Remove on AI preview list for robustness
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.revoke-btn');
    if (!btn) return;
    e.preventDefault();
    try {
        var card = btn.closest('.question-card');
        if (card && card.parentNode) {
            card.parentNode.removeChild(card);
        }
        var renumCards = document.querySelectorAll('#quizContainer .question-card');
        for (var j = 0; j < renumCards.length; j++) {
            var h4 = renumCards[j].querySelector('h4');
            if (h4) {
                var text = h4.textContent.replace(/^\s*Question\s*\d+:\s*/i, '');
                h4.textContent = 'Question ' + (j + 1) + ': ' + text;
            }
            renumCards[j].setAttribute('data-index', j);
        }
        showMessage('Question removed.', 'success');
    } catch (err) {
        showMessage('Failed to remove: ' + err.message, 'error');
    }
});