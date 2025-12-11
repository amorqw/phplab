<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Мини-отладка
echo "<div style='background:#f0f0f0; padding:10px; margin-bottom:20px;'>";
echo "<b>Отладка:</b> Сессия: " . session_id();
echo " | Метод: " . $_SERVER['REQUEST_METHOD'];
echo "</div>";

require_once 'db_connect.php';

// Проверка БД
$questions_count = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
echo "<div style='background:#e6ffe6; padding:10px; margin-bottom:20px;'>";
echo "В базе найдено вопросов: <b>$questions_count</b>";
echo "</div>";

if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = uniqid('quiz_', true);
}
$sessionId = $_SESSION['session_id'];

$message = '';

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    echo "<div style='background:#fffacd; padding:10px; margin-bottom:20px;'>";
    echo "<b>Обрабатываем форму...</b><br>";

    $all_saved = true;

    // Получаем вопросы
    $stmt = $pdo->query("SELECT id FROM questions");
    $questions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($questions as $question_id) {
        $input_name = "q_" . $question_id;

        if (isset($_POST[$input_name]) && !empty($_POST[$input_name])) {
            $answer = $_POST[$input_name];

            // Сохраняем ответ
            $sql = "INSERT INTO responses (questions_id, session_id, answer_text) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$question_id, $sessionId, $answer]);

            echo "✓ Ответ на вопрос $question_id сохранен<br>";
        } else {
            echo "✗ Нет ответа на вопрос $question_id<br>";
            $all_saved = false;
        }
    }

    echo "</div>";

    if ($all_saved) {
        $message = "✅ Спасибо! Все ответы сохранены.";
    } else {
        $message = "⚠️ Ответьте на все вопросы!";
    }
}

// Получаем вопросы для формы
$stmt = $pdo->query("SELECT id, text FROM questions");
$questions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Опросник</title>
</head>
<body>

<h1>Опросник</h1>

<?php if ($message): ?>
    <p style="padding:10px; background:#e0ffe0; border:1px solid green;">
        <?php echo $message; ?>
    </p>
<?php endif; ?>

<form method="POST">
    <?php foreach ($questions as $question): ?>
        <div style="margin-bottom:20px; border:1px solid #ccc; padding:15px;">
            <h3>Вопрос ID: <?php echo $question['id']; ?></h3>
            <p><?php echo htmlspecialchars($question['text']); ?></p>

            <textarea name="q_<?php echo $question['id']; ?>"
                      rows="4"
                      cols="50"
                      placeholder="Ваш ответ..."
                      style="width:100%; max-width:600px;">
                <?php echo isset($_POST['q_' . $question['id']]) ? htmlspecialchars($_POST['q_' . $question['id']]) : ''; ?>
            </textarea>

            <p><small>Имя поля в форме: q_<?php echo $question['id']; ?></small></p>
        </div>
    <?php endforeach; ?>

    <button type="submit" name="submit_quiz" style="padding:10px 20px;">
        Отправить ответы
    </button>
</form>

</body>
</html>