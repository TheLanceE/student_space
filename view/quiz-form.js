// Quiz Form Handler - Pure PHP/MySQL version
var form = document.getElementById("quizform");
var questionCount = 0;

// Validation flags
var quizTitle = false;
var quizCategory = false;
var quizGrade = false;
var passingGrade = false;
var questionsValid = false;

// Real-time verification of quiz title
document.getElementById("quizTitle").onkeyup = function () {
    var value = this.value.replace(/^\s+|\s+\$/g, '');
    var msg = document.getElementById("quizTitle_error");

    if (value.length >= 3) {
        msg.style.color = "green";
        msg.innerHTML = "Valid";
        quizTitle = true;
    } else {
        msg.style.color = "red";
        msg.innerHTML = "Quiz title must contain at least 3 characters";
        quizTitle = false;
    }
};

// Verification of category on change
document.getElementById("quizCategory").onchange = function () {
    var value = this.value;
    var msg = document.getElementById("quizCategory_error");

    if (value !== "") {
        msg.style.color = "green";
        msg.innerHTML = "Valid";
        quizCategory = true;
    } else {
        msg.style.color = "red";
        msg.innerHTML = "Please select a category";
        quizCategory = false;
    }
};

// Verification of grade level on change
document.getElementById("quizGrade").onchange = function () {
    var value = this.value;
    var msg = document.getElementById("quizGrade_error");

    if (value !== "") {
        msg.style.color = "green";
        msg.innerHTML = "Valid";
        quizGrade = true;
    } else {
        msg.style.color = "red";
        msg.innerHTML = "Please select a grade level";
        quizGrade = false;
    }
};

// Real-time verification of passing grade
document.getElementById("passingGrade").onkeyup = function () {
    var value = parseInt(this.value, 10);
    var msg = document.getElementById("passingGrade_error");

    if (!isNaN(value) && value >= 1 && value <= 100) {
        msg.style.color = "green";
        msg.innerHTML = "Valid";
        passingGrade = true;
    } else {
        msg.style.color = "red";
        msg.innerHTML = "Passing grade must be between 1 and 100";
        passingGrade = false;
    }
};

// Question validation function
function validateQuestions() {
    var questions = document.getElementsByClassName('question-item');
    var msg = document.getElementById("questions_error");

    if (questions.length === 0) {
        msg.style.color = "red";
        msg.innerHTML = "Please add at least one question";
        questionsValid = false;
        return;
    }

    var allQuestionsHaveCorrectAnswer = true;
    var allQuestionsHaveText = true;
    var allOptionsFilled = true;

    for (var i = 0; i < questions.length; i++) {
        var question = questions[i];
        var questionText = question.getElementsByClassName('question-text')[0].value.replace(/^\s+|\s+\$/g, '');
        var hasCorrectAnswer = false;

        // Check for correct answer
        var correctInputs = question.getElementsByClassName('correct-input');
        for (var j = 0; j < correctInputs.length; j++) {
            if (correctInputs[j].value === '1') {
                hasCorrectAnswer = true;
                break;
            }
        }

        // Check question text
        if (questionText.length < 1) {
            allQuestionsHaveText = false;
            question.getElementsByClassName('question-text')[0].style.border = "1px solid red";
        } else {
            question.getElementsByClassName('question-text')[0].style.border = "1px solid #ddd";
        }

        // Check correct answer
        if (!hasCorrectAnswer) {
            allQuestionsHaveCorrectAnswer = false;
            question.style.border = "2px solid red";
        } else {
            question.style.border = "1px solid #e0e0e0";
        }

        // Check options
        var optionsFilled = true;
        var optionInputs = question.getElementsByClassName('option-input');
        for (var k = 0; k < optionInputs.length; k++) {
            var optionValue = optionInputs[k].value.replace(/^\s+|\s+$/g, '');
            if (optionValue === "") {
                optionsFilled = false;
                optionInputs[k].style.border = "1px solid red";
            } else {
                optionInputs[k].style.border = "1px solid #ddd";
            }
        }

        if (!optionsFilled) allOptionsFilled = false;
    }

    if (!allQuestionsHaveText) {
        msg.style.color = "red";
        msg.innerHTML = "All questions must have text (min. 1 character)";
        questionsValid = false;
    } else if (!allQuestionsHaveCorrectAnswer) {
        msg.style.color = "red";
        msg.innerHTML = "Please set correct answer for all questions";
        questionsValid = false;
    } else if (!allOptionsFilled) {
        msg.style.color = "red";
        msg.innerHTML = "Please fill all options for each question";
        questionsValid = false;
    } else {
        msg.style.color = "green";
        msg.innerHTML = "Valid (" + questions.length + " questions)";
        questionsValid = true;
    }
}

// Add new question
function addNewQuestion() {
    questionCount++;
    var questionHTML = '' +
    '<div class="question-item" id="question-' + questionCount + '">' +
    '<div class="question-header">' +
    '<div class="section-subheader">Question ' + questionCount + '</div>' +
    (questionCount > 1 ? '<button type="button" class="btn-danger btn-sm remove-question" id="remove-question-' + questionCount + '" style="float: right;"><i class="fas fa-trash"></i> Remove</button>' : '') +
    '<div class="clearfix"></div>' +
    '</div>' +
    '<div class="form-group">' +
        '<label class="form-label"><strong>Question Text *</strong></label>' +
        '<input type="text" class="form-control question-text" name="questions[' + questionCount + '][text]" placeholder="Enter your question">' +
        '<small class="text-muted">Must be at least 1 character</small>' + 
    '</div>' +
    '<div class="option-group">' +
        '<label class="form-label"><strong>Options *</strong></label>';

    var optionLetters = ['A', 'B', 'C', 'D'];
    for (var i = 0; i < optionLetters.length; i++) {
        var letter = optionLetters[i];
        questionHTML += '' +
        '<div class="option-row">' +
        '<div class="option-label">' + letter + '</div>' +
        '<input type="text" class="form-control option-input option-' + letter.toLowerCase() + '" name="questions[' + questionCount + '][options][' + i + '][text]" placeholder="Option ' + letter + '">' +
        '<div class="correct-option">' +
        '<button type="button" class="btn-outline-primary btn-sm set-correct" id="set-correct-Q' + questionCount + '-O' + letter + '">' +
        'Set Correct' +
        '</button>' +
        '<input type="hidden" class="correct-input" name="questions[' + questionCount + '][options][' + i + '][is_correct]" value="0">' +
        '</div>' +
        '<div class="clearfix"></div>' +
        '</div>';
    }

    questionHTML += '' +
    '</div>' +
    '<div class="correct-answer-display">' +
        '<small class="text-muted">Correct answer: <span id="correct-answer-' + questionCount + '">Not set</span></small>' +
    '</div>' +
    '</div>';

    var container = document.getElementById('questionsContainer');
    var newQuestion = document.createElement('div');
    newQuestion.innerHTML = questionHTML;
    container.appendChild(newQuestion);

    // Add event listeners for the new question
    addQuestionEventListeners(questionCount);
    validateQuestions();
}

function addQuestionEventListeners(questionId) {
    var questionElement = document.getElementById('question-' + questionId);
    if (!questionElement) return;

    // Add event listeners to question text and options
    var questionText = questionElement.getElementsByClassName('question-text')[0];
    if (questionText) {
        questionText.onkeyup = validateQuestions;
    }

    var optionInputs = questionElement.getElementsByClassName('option-input');
    for (var i = 0; i < optionInputs.length; i++) {
        optionInputs[i].onkeyup = validateQuestions;
    }

    // Add event listeners to set-correct buttons
    var setCorrectButtons = questionElement.getElementsByClassName('set-correct');
    for (var j = 0; j < setCorrectButtons.length; j++) {
        setCorrectButtons[j].onclick = function() {
            var parts = this.id.split('-');
            var qPart = parts[2] || '';
            var oPart = parts[3] || '';
            var qid = qPart.replace(/^Q/, '');
            var opt = oPart.replace(/^O/, '');
            setCorrectAnswer(qid, opt);
        };
    }

    // Add event listener to remove button
    var removeBtn = questionElement.getElementsByClassName('remove-question')[0];
    if (removeBtn) {
        removeBtn.onclick = function() {
            var qid = this.id.replace('remove-question-', '');
            removeQuestion(qid);
        };
    }
}

function setCorrectAnswer(questionId, optionLetter) {
    var questionElement = document.getElementById('question-' + questionId);
    if (!questionElement) return;

    // Reset all options for this question
    var setCorrectButtons = questionElement.getElementsByClassName('set-correct');
    var correctInputs = questionElement.getElementsByClassName('correct-input');

    for (var i = 0; i < setCorrectButtons.length; i++) {
        var btn = setCorrectButtons[i];
        btn.innerHTML = 'Set Correct';
        btn.className = 'btn-outline-primary btn-sm set-correct';

        if (correctInputs[i]) {
            correctInputs[i].value = '0';
        }
    }

    // Set this option as correct
    var correctButton = document.getElementById('set-correct-Q' + questionId + '-O' + optionLetter);
    if (correctButton) {
        correctButton.innerHTML = 'Correct';
        correctButton.className = 'btn-success btn-sm set-correct';

        var correctHiddenInput = correctButton.nextElementSibling;
        if (correctHiddenInput && correctHiddenInput.type === 'hidden') {
            correctHiddenInput.value = '1';
        }

        // Update display
        var correctAnswerDisplay = document.getElementById('correct-answer-' + questionId);
        if (correctAnswerDisplay) {
            correctAnswerDisplay.innerHTML = 'Option ' + optionLetter;
            correctAnswerDisplay.style.color = 'green';
            correctAnswerDisplay.style.fontWeight = 'bold';
        }
    }

    validateQuestions();
}

function removeQuestion(questionId) {
    var questionElement = document.getElementById('question-' + questionId);
    if (questionElement && questionElement.parentNode) {
        questionElement.parentNode.removeChild(questionElement);
        renumberQuestions();
        validateQuestions();
    }
}

function renumberQuestions() {
    var questions = document.getElementsByClassName('question-item');
    questionCount = questions.length;

    for (var i = 0; i < questions.length; i++) {
        var questionId = i + 1;
        questions[i].id = 'question-' + questionId;

        var header = questions[i].getElementsByClassName('section-subheader')[0];
        if (header) {
            header.innerHTML = 'Question ' + questionId;
        }

        // Update all form field names with new question number
        var questionText = questions[i].getElementsByClassName('question-text')[0];
        if (questionText) {
            questionText.name = 'questions[' + questionId + '][text]';
        }

        var optionLetters = ['A','B','C','D'];
        var optionInputs = questions[i].getElementsByClassName('option-input');
        var correctInputs = questions[i].getElementsByClassName('correct-input');
        
        for (var j = 0; j < optionInputs.length; j++) {
            optionInputs[j].name = 'questions[' + questionId + '][options][' + j + '][text]';
            correctInputs[j].name = 'questions[' + questionId + '][options][' + j + '][is_correct]';
        }

        // Update button IDs
        for (var ol = 0; ol < optionLetters.length; ol++) {
            var letter = optionLetters[ol];
            var inputs = questions[i].getElementsByClassName('option-' + letter.toLowerCase());
            if (inputs.length > 0) {
                var optionRow = inputs[0].parentNode;
                var btns = optionRow.getElementsByClassName('set-correct');
                if (btns.length > 0) {
                    btns[0].id = 'set-correct-Q' + questionId + '-O' + letter;
                }
            }
        }

        var removeButtons = questions[i].getElementsByClassName('remove-question');
        for (var k = 0; k < removeButtons.length; k++) {
            var removeBtn = removeButtons[k];
            removeBtn.id = 'remove-question-' + questionId;
        }

        // Update correct answer display ID
        var correctDisplay = questions[i].getElementsByClassName('correct-answer-display')[0];
        if (correctDisplay) {
            var span = correctDisplay.getElementsByTagName('span')[0];
            if (span) {
                span.id = 'correct-answer-' + questionId;
            }
        }
    }
}

// Submit quiz form via traditional form submission
function submitQuizForm() {
    // Final validation check
    validateQuestions();

    if (quizTitle && quizCategory && quizGrade && passingGrade && questionsValid) {
        // Submit the form normally
        form.submit();
    } else {
        var formMessage = document.getElementById('form_message');
        if (formMessage) {
            formMessage.style.color = 'red';
            formMessage.innerHTML = 'Please fix all validation errors before submitting!';
        } else {
            alert("Please fix all validation errors before submitting!");
        }
    }
}

// Function to show initial validation errors
function showInitialValidationErrors() {
    // Show error for quiz title
    var titleMsg = document.getElementById("quizTitle_error");
    if (titleMsg) {
        titleMsg.style.color = "red";
        titleMsg.innerHTML = "Quiz title must contain at least 3 characters";
    }
    
    // Show error for category
    var categoryMsg = document.getElementById("quizCategory_error");
    if (categoryMsg) {
        categoryMsg.style.color = "red";
        categoryMsg.innerHTML = "Please select a category";
    }
    
    // Show error for grade level
    var gradeMsg = document.getElementById("quizGrade_error");
    if (gradeMsg) {
        gradeMsg.style.color = "red";
        gradeMsg.innerHTML = "Please select a grade level";
    }
    
    // Show error for passing grade
    var passingMsg = document.getElementById("passingGrade_error");
    if (passingMsg) {
        passingMsg.style.color = "red";
        passingMsg.innerHTML = "Passing grade must be between 1 and 100";
    }
    
    // Show error for questions
    var questionsMsg = document.getElementById("questions_error");
    if (questionsMsg) {
        questionsMsg.style.color = "red";
        questionsMsg.innerHTML = "Please add at least one question";
    }
}

// Initialize when page loads
if (window.addEventListener) {
    window.addEventListener('load', function() {
        document.getElementById('addQuestionBtn').onclick = addNewQuestion;
        document.getElementById('submitQuizBtn').onclick = submitQuizForm;
        document.getElementById('cancelQuizBtn').onclick = function() {
            if (confirm('Are you sure you want to cancel? All unsaved changes will be lost.')) {
                window.location.reload();
            }
        };
        addNewQuestion(); // Start with one question
        showInitialValidationErrors(); // Show validation errors on load
    });
}

// Global delegation for remove buttons to support dynamically inserted questions
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.remove-question');
    if (!btn) return;
    e.preventDefault();
    // Try to find question id by button id or closest question-item
    var qid = null;
    if (btn.id && btn.id.indexOf('remove-question-') === 0) {
        qid = btn.id.replace('remove-question-', '');
    } else {
        var qEl = btn.closest('.question-item');
        if (qEl && qEl.id && qEl.id.indexOf('question-') === 0) {
            qid = qEl.id.replace('question-', '');
        }
    }
    if (qid) {
        removeQuestion(qid);
    }
});

// Fallback: handle generic "Remove" buttons even if class is missing
document.addEventListener('click', function(e) {
    var el = e.target;
    var btn = el.closest('button');
    if (!btn) return;
    var isDanger = /btn-danger/.test(btn.className || '');
    var hasRemoveText = (btn.textContent || '').trim().toLowerCase() === 'remove';
    if (!isDanger || !hasRemoveText) return;
    e.preventDefault();
    var qEl = btn.closest('.question-item');
    if (qEl && qEl.id && qEl.id.indexOf('question-') === 0) {
        var qid = qEl.id.replace('question-', '');
        removeQuestion(qid);
    }
});