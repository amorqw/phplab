<?php
require_once 'db_connect.php';

session_start();
if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = uniqid('quiz_', true);
    $_SESSION['submitted'] = false;
}
$sessionId = $_SESSION['session_id'];

$message = '';

// Получаем вопросы
$stmt = $pdo->query("SELECT id, text FROM questions ORDER BY id");
$questions = $stmt->fetchAll();

// Обработка POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {

    if ($_SESSION['submitted']) {
        $message = "⚠️ Вы уже отправили ответы.";
    } else {
        $all_filled = true;
        $answers_to_save = [];

        foreach ($questions as $question) {
            $input_name = "q_" . $question['id'];
            if (!isset($_POST[$input_name]) || empty(trim($_POST[$input_name]))) {
                $all_filled = false;
            } else {
                $answers_to_save[$question['id']] = trim($_POST[$input_name]);
            }
        }

        if ($all_filled) {
            foreach ($answers_to_save as $question_id => $answer) {
                $sql = "INSERT INTO user_responses (question_id, session_id, answer_text) 
                        VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$question_id, $sessionId, $answer]);
            }
            $_SESSION['submitted'] = true;
            $message = "✅ Спасибо! Ваши ответы сохранены.";
        } else {
            $message = "⚠️ Пожалуйста, ответьте на все вопросы.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Опросник на PHP</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .question { margin-bottom: 20px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        .message { padding: 10px; margin: 20px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px 30px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>

<h1>Опросник</h1>

<?php if ($message): ?>
    <div class="message <?php echo (isset($_SESSION['submitted']) ? $_SESSION['submitted'] : false) ? 'success' : 'warning'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if (!(isset($_SESSION['submitted']) ? $_SESSION['submitted'] : false)): ?>
    <form method="POST" action="index.php">
        <?php foreach ($questions as $question): ?>
            <div class="question">
                <h3><?php echo htmlspecialchars($question['text']); ?></h3>
                <textarea name="q_<?php echo $question['id']; ?>"
                          rows="4"
                          required><?php
                    if (isset($_POST['submit_quiz']) && isset($_POST["q_" . $question['id']])) {
                        echo htmlspecialchars($_POST["q_" . $question['id']]);
                    }
                    ?></textarea>
            </div>
        <?php endforeach; ?>

        <button type="submit" name="submit_quiz">Отправить ответы</button>
    </form>
<?php else: ?>
    <p>Вы уже завершили опрос. Спасибо за участие!</p>
<?php endif; ?>

</body>
</html>