<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load quizzes when page loads
    loadQuizzes();
});

function loadQuizzes() {
    fetch('../controller/quizcontroller.php?action=getAllQuizzes')
        .then(response => response.text())
        .then(html => {
            document.getElementById('quizzesContainer').innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('quizzesContainer').innerHTML = 
                '<div class="alert alert-danger">Error loading quizzes. Please try again later.</div>';
        });
}
</script>