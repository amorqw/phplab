<?php
require_once 'db_connect.php';

// Устанавливаем уникальный идентификатор для сессии (для группировки ответов)
session_start();
if (!isset($_SESSION['session_id'])) {
// В PHP часто используют встроенные сессии или просто уникальные идентификаторы
$_SESSION['session_id'] = uniqid('quiz_', true);
}
$sessionId = $_SESSION['session_id'];

$message = '';

// --- 2. ОБРАБОТКА POST-ЗАПРОСА ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {

// Получаем все вопросы из БД для проверки
$stmt = $pdo->query("SELECT id FROM questions");
$questions = $stmt->fetchAll(PDO::FETCH_COLUMN);

$all_saved = true;

// Вставляем каждый ответ в БД
foreach ($questions as $questions_id) {
$input_name = "q_" . $questions_id;

// Проверяем, был ли ответ на этот вопрос
if (isset($_POST[$input_name]) && !empty($_POST[$input_name])) {
$answer = $_POST[$input_name];

// Используем подготовленные запросы для безопасности (защита от SQL-инъекций)
$sql = "INSERT INTO user_responses (questions_id, session_id, answer_text) VALUES (?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$questions_id, $sessionId, $answer]);
} else {
$all_saved = false;
}
}

if ($all_saved) {
$message = "✅ Спасибо! Ваши ответы сохранены.";
// После сохранения перенаправляем пользователя на страницу результатов или благодарности
// header('Location: results.php');
// exit();
} else {
$message = "⚠️ Пожалуйста, ответьте на все вопросы.";
}
}

// --- 3. ПОЛУЧЕНИЕ ВОПРОСОВ ДЛЯ ОТОБРАЖЕНИЯ ---
$stmt = $pdo->query("SELECT id, text FROM questions");
$questions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<body>

<h1>Опросник</h1>

<?php if ($message): ?>
    <p style="padding: 10px; background: #e0ffe0; border: 1px solid #0a0;">
        <?php echo $message; ?>
    </p>
<?php endif; ?>

<form method="POST" action="index.php">

    <?php foreach ($questions as $question): ?>
        <div style="margin-bottom: 20px; border: 1px solid #ccc; padding: 15px;">
            <h3><?php echo htmlspecialchars($question['text']); ?></h3>

            <textarea name="q_<?php echo $question['id']; ?>" rows="4" cols="50" required></textarea>
        </div>
    <?php endforeach; ?>

    <button type="submit" name="submit_quiz" style="padding: 10px 20px;">Отправить ответы</button>
</form>

</body>
</html>