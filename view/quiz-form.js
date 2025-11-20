// quiz-form.js - HTML4 compatible version
var form = document.getElementById("quizForm");

// Validation flags
var quizTitle = false;
var quizCategory = false;
var quizGrade = false;
var passingGrade = false;
var questionsValid = false;

// Real-time verification of quiz title
document.getElementById("quizTitle").onkeyup = function () {
  var value = this.value.replace(/^\s+|\s+$/g, '');
  var msg = document.getElementById("quizTitle_error");

  if (value.length >= 3) {
    msg.style.color = "green";
    msg.innerHTML = "&#x2705; Valid";
    quizTitle = true;
  } else {
    msg.style.color = "red";
    msg.innerHTML = "&#x274C; Quiz title must contain at least 3 characters";
    quizTitle = false;
  }
};

// Verification of category on change
document.getElementById("quizCategory").onchange = function () {
  var value = this.value;
  var msg = document.getElementById("quizCategory_error");

  if (value !== "") {
    msg.style.color = "green";
    msg.innerHTML = "&#x2705; Valid";
    quizCategory = true;
  } else {
    msg.style.color = "red";
    msg.innerHTML = "&#x274C; Please select a category";
    quizCategory = false;
  }
};

// Verification of grade level on change
document.getElementById("quizGrade").onchange = function () {
  var value = this.value;
  var msg = document.getElementById("quizGrade_error");

  if (value !== "") {
    msg.style.color = "green";
    msg.innerHTML = "&#x2705; Valid";
    quizGrade = true;
  } else {
    msg.style.color = "red";
    msg.innerHTML = "&#x274C; Please select a grade level";
    quizGrade = false;
  }
};

// Real-time verification of passing grade
document.getElementById("passingGrade").onkeyup = function () {
  var value = parseInt(this.value, 10);
  var msg = document.getElementById("passingGrade_error");

  if (!isNaN(value) && value >= 1 && value <= 100) {
    msg.style.color = "green";
    msg.innerHTML = "&#x2705; Valid";
    passingGrade = true;
  } else {
    msg.style.color = "red";
    msg.innerHTML = "&#x274C; Passing grade must be between 1 and 100";
    passingGrade = false;
  }
};

// Question validation function
function validateQuestions() {
  var questions = document.getElementsByClassName('question-item');
  var msg = document.getElementById("questions_error");
  
  if (questions.length === 0) {
    msg.style.color = "red";
    msg.innerHTML = "&#x274C; Please add at least one question";
    questionsValid = false;
    return;
  }

  var allQuestionsHaveCorrectAnswer = true;
  var allQuestionsHaveText = true;
  var allOptionsFilled = true;

  for (var i = 0; i < questions.length; i++) {
    var question = questions[i];
    var questionText = question.getElementsByClassName('question-text')[0].value.replace(/^\s+|\s+$/g, '');
    var hiddenInputs = question.getElementsByTagName('input');
    var hasCorrectAnswer = false;
    
    // Check for correct answer (look for hidden input with value "1")
    for (var j = 0; j < hiddenInputs.length; j++) {
      if (hiddenInputs[j].type === 'hidden' && hiddenInputs[j].value === '1') {
        hasCorrectAnswer = true;
        break;
      }
    }
    
    // Check question text - UPDATED: Minimum 1 character (allows numbers and short text like "2+2")
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
    msg.innerHTML = "&#x274C; All questions must have text (min. 1 character)"; // UPDATED: Changed to 1 character
    questionsValid = false;
  } else if (!allQuestionsHaveCorrectAnswer) {
    msg.style.color = "red";
    msg.innerHTML = "&#x274C; Please set correct answer for all questions";
    questionsValid = false;
  } else if (!allOptionsFilled) {
    msg.style.color = "red";
    msg.innerHTML = "&#x274C; Please fill all options for each question";
    questionsValid = false;
  } else {
    msg.style.color = "green";
    msg.innerHTML = "&#x2705; Valid (" + questions.length + " questions)";
    questionsValid = true;
  }
}

// Question management functions
var questionCount = 0;

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
        '<input type="text" class="form-control question-text" placeholder="Enter your question">' +
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
        '<input type="text" class="form-control option-input option-' + letter.toLowerCase() + '" placeholder="Option ' + letter + '">' +
        '<div class="correct-option">' +
          '<button type="button" class="btn-outline-primary btn-sm set-correct" id="set-correct-Q' + questionCount + '-O' + letter + '">' +
            'Set Correct' +
          '</button>' +
          '<input type="hidden" class="correct-input" value="0">' +
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
      // id format: set-correct-Q{questionId}-O{letter}
      var parts = this.id.split('-');
      // parts => ['set','correct','Q{questionId}','O{letter}']
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
        // id format: remove-question-{id}
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
    correctButton.innerHTML = 'Correct &#x2713;';
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
    
    // Update all buttons with new question number
    // Update set-correct button IDs based on option letter
    var optionLetters = ['A','B','C','D'];
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

function submitQuizForm() {
  // Final validation check
  validateQuestions();
  
  if (quizTitle && quizCategory && quizGrade && passingGrade && questionsValid) {
    // Create data object manually
    var data = {
      title: document.getElementById('quizTitle').value,
      category: document.getElementById('quizCategory').value,
      grade: document.getElementById('quizGrade').value,
      passing_grade: parseInt(document.getElementById('passingGrade').value, 10),
      description: document.getElementById('quizDescription').value,
      status: getSelectedRadioValue('status'),
      time_limit: document.getElementById('timeLimit').value ? parseInt(document.getElementById('timeLimit').value, 10) : null,
      questions: []
    };
    
    // Collect questions data (send options as an ARRAY of objects to match server-side expectation)
    var questions = document.getElementsByClassName('question-item');
    for (var i = 0; i < questions.length; i++) {
      var questionId = questions[i].id ? questions[i].id.replace('question-','') : (i+1);
      var questionText = questions[i].getElementsByClassName('question-text')[0].value;
      var optionsArr = [];
      
      var optionLetters = ['A', 'B', 'C', 'D'];
      for (var j = 0; j < optionLetters.length; j++) {
        var letter = optionLetters[j];
        var optionInputs = questions[i].getElementsByClassName('option-' + letter.toLowerCase());
        if (optionInputs.length > 0) {
          var optionRow = optionInputs[0].parentNode;
          var hiddenInput = optionRow.querySelector('.correct-input');
          var isCorrect = !!(hiddenInput && hiddenInput.value === '1');

          // push object with label/text/is_correct to match PHP controller expectation
          optionsArr.push({
              label: letter,
            text: optionInputs[0].value,
            is_correct: isCorrect
          });
        }
      }

      data.questions.push({
        text: questionText,
        options: optionsArr
      });
    }
    
    // Send to server using XMLHttpRequest
    var xhr = new XMLHttpRequest();
    // fix controller path (view folder -> controller folder)
    xhr.open('POST', '../controller/quizcontroller.php?action=createQuiz', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4) {
        var formMessage = document.getElementById('form_message');
        try {
          var result = JSON.parse(xhr.responseText);
          if (result.success) {
            // show inline success message
            if (formMessage) {
              formMessage.style.color = 'green';
              formMessage.innerHTML = '\u2705 Quiz created successfully!';
            }

            // add to local UI arrays if available
            try {
              var newQuiz = {
                id: result.quizId || result.quiz_id || ('quiz-' + (new Date()).getTime()),
                title: data.title,
                category: data.category,
                gradeLevel: data.grade,
                description: data.description,
                questionCount: data.questions.length,
                quizStatus: data.status,
                createdBy: 'you',
                createdDate: (new Date()).toLocaleDateString(),
                questions: data.questions,
                passingGrade: data.passing_grade,
                timeLimit: data.time_limit
              };

              if (typeof myQuizzes !== 'undefined') {
                myQuizzes.push(newQuiz);
              }
              if (typeof allQuizzes !== 'undefined') {
                allQuizzes.push(newQuiz);
              }

              // Mark this quiz id in a cookie so it persists across refresh (non-HTML5 storage)
              try {
                var createdIds = (document.cookie.replace(/(?:(?:^|.*;\s*)myCreatedQuizzes\s*\=\s*([^;]*).*$)|^.*$/, "$1") || '');
                var arr = createdIds ? createdIds.split(',') : [];
                arr.push(newQuiz.id);
                // store cookie for 7 days
                var exdate = new Date();
                exdate.setDate(exdate.getDate() + 7);
                document.cookie = 'myCreatedQuizzes=' + arr.join(',') + '; expires=' + exdate.toUTCString() + '; path=/';
              } catch (e) {}

              // Refresh UI lists and stats if functions exist
              if (typeof displayMyQuizzes === 'function') displayMyQuizzes();
              if (typeof displayBrowseQuizzes === 'function') displayBrowseQuizzes();
              if (typeof updateStats === 'function') updateStats();
            } catch (e) {
              // ignore failures to update local UI
            }

            // Optionally reset the form after a short delay
            setTimeout(function() {
              if (form && form.reset) form.reset();
              // clear questions container and re-add one question
              var qc = document.getElementById('questionsContainer');
              if (qc) qc.innerHTML = '';
              questionCount = 0;
              addNewQuestion();
            }, 800);
          } else {
            if (formMessage) {
              formMessage.style.color = 'red';
              formMessage.innerHTML = '\u274C Error: ' + (result.message || 'Failed to create quiz') + ' (HTTP ' + xhr.status + ')';
              // show server response for debugging
              formMessage.innerHTML += '<br><small>' + (xhr.responseText || '') + '</small>';
            } else {
              alert('Error: ' + (result.message || 'Failed to create quiz'));
            }
          }
        } catch (e) {
          // parsing failed or server returned non-JSON - show status and responseText for debugging
          if (formMessage) {
            formMessage.style.color = 'red';
            formMessage.innerHTML = '\u274C Failed to create quiz. Server returned HTTP ' + xhr.status + '.';
            formMessage.innerHTML += '<br><small>' + (xhr.responseText || '') + '</small>';
          } else {
            alert('Failed to create quiz. Server returned HTTP ' + xhr.status);
          }
          console.error('Create quiz XHR error, status:', xhr.status, 'response:', xhr.responseText);
        }
      }
    };
    
    xhr.send(JSON.stringify(data));
  } else {
    var formMessage = document.getElementById('form_message');
    if (formMessage) {
      formMessage.style.color = 'red';
      formMessage.innerHTML = '\u274C Please fix all validation errors before submitting!';
    } else {
      alert("Please fix all validation errors before submitting!");
    }
  }
}

// (No local fallback; server must persist quizzes for strict non-HTML5 behavior)

// Helper function to get selected radio value
function getSelectedRadioValue(name) {
  var radios = document.getElementsByName(name);
  for (var i = 0; i < radios.length; i++) {
    if (radios[i].checked) {
      return radios[i].value;
    }
  }
  return '';
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
  });
} else if (window.attachEvent) {
  window.attachEvent('onload', function() {
    document.getElementById('addQuestionBtn').onclick = addNewQuestion;
    document.getElementById('submitQuizBtn').onclick = submitQuizForm;
    document.getElementById('cancelQuizBtn').onclick = function() {
      if (confirm('Are you sure you want to cancel? All unsaved changes will be lost.')) {
        window.location.reload();
      }
    };
    addNewQuestion(); // Start with one question
  });
}