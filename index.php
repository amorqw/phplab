<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once 'db_connect.php';

if (isset($_POST['submit_quiz'])) {

    if (!isset($_SESSION['session_id'])) {
        $_SESSION['session_id'] = uniqid();
    }
    $session_id = $_SESSION['session_id'];

    $stmt = $pdo->query("SELECT id FROM questions");
    $questions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $all_answered = true;
    foreach ($questions as $question_id) {
        $answer = isset($_POST['q_' . $question_id]) ? $_POST['q_' . $question_id] : '';

        if (!empty($answer)) {
            $sql = "INSERT INTO responses (questions_id, session_id, answer_text) 
                    VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$question_id, $session_id, $answer]);
        } else {
            $all_answered = false;
        }
    }

    if ($all_answered) {
        echo "<p style='color:green;'>Спасибо</p>";
    } else {
        echo "<p style='color:red;'>Ответьте на все вопросы</p>";
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
            <textarea name="q_<?php echo $question['id']; ?>" rows="4" cols="50"></textarea>
        </div>
    <?php endforeach; ?>

    <button type="submit" name="submit_quiz">Отправить</button>

    <div style="margin-top: 20px;">
        <a href="responses.php" style="color: blue">
            Посмотреть все ответы
        </a>
    </div>

</form>

</body>
</html>