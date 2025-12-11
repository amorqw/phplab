<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = uniqid('quiz_', true);
}
$sessionId = $_SESSION['session_id'];

require_once 'db_connect.php';

if (isset($_POST['submit_quiz'])) {

    $stmt = $pdo->query("SELECT id FROM questions");
    $questions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $all_answered = true;
    $saved_count = 0;

    foreach ($questions as $question_id) {
        if (empty($_POST['q_'.$question_id])) {
            $all_answered = false;
        }
    }

    if ($all_answered) {
        foreach ($questions as $question_id) {
            $answer = trim($_POST['q_'.$question_id]);

            $sql = "INSERT INTO responses (questions_id, session_id, answer_text) 
                    VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
        }

        echo "<p style='color:green;'>Сохранено </p>";


    } else {
        echo "<p style='color:red;'> Ответьте на все вопросы</p>";
    }
}

$stmt = $pdo->query("SELECT id, text FROM questions");
$questions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Опросник</title>
</head>
<body>

<h1>Опросник</h1>

<form method="POST">

    <?php foreach ($questions as $question): ?>
        <div style="margin: 20px 0; padding: 15px; border: 1px solid #ccc;">
            <p><b><?php echo $question['text']; ?></b></p>
            <textarea name="q_<?php echo $question['id']; ?>" rows="4" cols="50"><?php
                if (isset($_POST['submit_quiz']) && isset($_POST['q_'.$question['id']])) {
                    echo htmlspecialchars($_POST['q_'.$question['id']]);
                }
                ?></textarea>
        </div>
    <?php endforeach; ?>

    <button type="submit" name="submit_quiz">Отправить</button>

</form>

<hr>
</body>
</html>