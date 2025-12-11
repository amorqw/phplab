<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc; margin-bottom: 20px;'>";
echo "=== DEBUG INFO ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "POST Data: " . print_r($_POST, true) . "\n";
echo "===============\n</pre>";

// Подключение к БД с отладкой
try {
    require_once 'db_connect.php';
    echo "<p style='color: green;'>✅ База данных подключена успешно</p>";

    // Проверим подключение
    $pdo->query("SELECT 1");
    echo "<p style='color: green;'>✅ Запрос к БД выполнен успешно</p>";

    // Проверим существование таблиц
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Таблицы в БД: " . implode(', ', $tables) . "</p>";

    // Проверим структуру таблиц
    if (in_array('questions', $tables)) {
        $questionsCount = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
        echo "<p>Количество вопросов в таблице questions: " . $questionsCount . "</p>";

        $questionsStructure = $pdo->query("DESCRIBE questions")->fetchAll();
        echo "<p>Структура таблицы questions:<br>";
        foreach ($questionsStructure as $column) {
            echo "{$column['Field']} ({$column['Type']})<br>";
        }
        echo "</p>";
    }

    if (in_array('user_responses', $tables)) {
        $responsesCount = $pdo->query("SELECT COUNT(*) FROM user_responses")->fetchColumn();
        echo "<p>Количество ответов в таблице user_responses: " . $responsesCount . "</p>";
    }

} catch (PDOException $e) {
    die("<p style='color: red;'>❌ Ошибка подключения к БД: " . $e->getMessage() . "</p>");
}

// Устанавливаем уникальный идентификатор для сессии
if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = uniqid('quiz_', true);
}
$sessionId = $_SESSION['session_id'];
echo "<p>Session ID для опроса: $sessionId</p>";

$message = '';

// --- ОБРАБОТКА POST-ЗАПРОСА ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    echo "<div style='background: #fffacd; padding: 10px; border: 1px solid #ffd700; margin: 10px 0;'>";
    echo "<h3>🔍 ОБРАБОТКА POST-ЗАПРОСА</h3>";

    // Получаем все вопросы из БД для проверки
    try {
        $stmt = $pdo->query("SELECT id, text FROM questions");
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<p>Найдено вопросов: " . count($questions) . "</p>";

        if (empty($questions)) {
            echo "<p style='color: red;'>❌ Нет вопросов в базе данных!</p>";
        } else {
            echo "<p>Вопросы из БД:<br>";
            foreach ($questions as $q) {
                echo "ID: {$q['id']}, Текст: {$q['text']}<br>";
            }
            echo "</p>";
        }

        $all_saved = true;
        $saved_answers = [];

        // Вставляем каждый ответ в БД
        foreach ($questions as $question) {
            $question_id = $question['id'];
            $input_name = "q_" . $question_id;

            echo "<p>Обработка вопроса ID: $question_id, имя поля: $input_name</p>";

            // Проверяем, был ли ответ на этот вопрос
            if (isset($_POST[$input_name]) && !empty(trim($_POST[$input_name]))) {
                $answer = trim($_POST[$input_name]);
                echo "<p>Получен ответ: " . htmlspecialchars(substr($answer, 0, 50)) . "...</p>";

                try {
                    // Используем подготовленные запросы для безопасности
                    $sql = "INSERT INTO user_responses (questions_id, session_id, answer_text) VALUES (?, ?, ?)";
                    $stmt = $pdo->prepare($sql);

                    echo "<p>Выполняем SQL: $sql с параметрами: $question_id, $sessionId, " .
                            htmlspecialchars(substr($answer, 0, 30)) . "...</p>";

                    $result = $stmt->execute([$question_id, $sessionId, $answer]);

                    if ($result) {
                        $last_id = $pdo->lastInsertId();
                        echo "<p style='color: green;'>✅ Ответ ID {$question_id} сохранен (ID записи: $last_id)</p>";
                        $saved_answers[] = $question_id;
                    } else {
                        echo "<p style='color: red;'>❌ Ошибка сохранения ответа ID {$question_id}</p>";
                        $all_saved = false;
                    }

                } catch (PDOException $e) {
                    echo "<p style='color: red;'>❌ Ошибка БД при сохранении: " . $e->getMessage() . "</p>";
                    $all_saved = false;
                }
            } else {
                echo "<p style='color: orange;'>⚠️ Нет ответа для вопроса ID: $question_id</p>";
                $all_saved = false;
            }
        }

        echo "<p>Сохранено ответов: " . count($saved_answers) . " из " . count($questions) . "</p>";

        if ($all_saved) {
            $message = "✅ Спасибо! Все ваши ответы сохранены успешно.";
            echo "<p style='color: green; font-weight: bold;'>" . $message . "</p>";
        } else {
            $message = "⚠️ Пожалуйста, ответьте на все вопросы.";
            echo "<p style='color: orange; font-weight: bold;'>" . $message . "</p>";
        }

    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Ошибка при получении вопросов: " . $e->getMessage() . "</p>";
        $message = "❌ Ошибка сервера: " . $e->getMessage();
    }

    echo "</div>";
}

// --- ПОЛУЧЕНИЕ ВОПРОСОВ ДЛЯ ОТОБРАЖЕНИЯ ---
echo "<div style='background: #e6f7ff; padding: 10px; border: 1px solid #91d5ff; margin: 10px 0;'>";
echo "<h3>📋 ПОЛУЧЕНИЕ ВОПРОСОВ ДЛЯ ФОРМЫ</h3>";

try {
    $stmt = $pdo->query("SELECT id, text FROM questions ORDER BY id");
    $questions = $stmt->fetchAll();

    echo "<p>Найдено вопросов для формы: " . count($questions) . "</p>";

} catch (PDOException $e) {
    die("<p style='color: red;'>❌ Ошибка при получении вопросов: " . $e->getMessage() . "</p>");
}

echo "</div>";
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Опросник - Отладочная версия</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .question {
            margin-bottom: 20px;
            border: 1px solid #ccc;
            padding: 15px;
            background: #fff;
            border-radius: 5px;
        }
        .question h3 {
            margin-top: 0;
            color: #333;
        }
        textarea {
            width: 100%;
            max-width: 600px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #45a049;
        }
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-weight: bold;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>

<h1>📝 Опросник - Отладочная версия</h1>

<?php if ($message): ?>
    <div class="message <?php
    echo strpos($message, '✅') !== false ? 'success' :
            (strpos($message, '⚠️') !== false ? 'warning' : 'error');
    ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if (empty($questions)): ?>
    <div class="message error">
        ❌ Нет вопросов для отображения. Проверьте базу данных.
    </div>
<?php else: ?>
    <form method="POST" action="">
        <?php foreach ($questions as $index => $question): ?>
            <div class="question">
                <h3>Вопрос <?php echo $index + 1; ?> (ID: <?php echo $question['id']; ?>):</h3>
                <p><?php echo htmlspecialchars($question['text']); ?></p>

                <textarea
                        name="q_<?php echo $question['id']; ?>"
                        rows="4"
                        placeholder="Введите ваш ответ здесь..."
                    <?php echo isset($_POST['submit_quiz']) && empty($_POST['q_' . $question['id']]) ? 'style="border-color: red;"' : ''; ?>
                ><?php echo isset($_POST['q_' . $question['id']]) ? htmlspecialchars($_POST['q_' . $question['id']]) : ''; ?></textarea>

                <p><small>Имя поля в форме: q_<?php echo $question['id']; ?></small></p>
            </div>
        <?php endforeach; ?>

        <div style="margin-top: 30px;">
            <button type="submit" name="submit_quiz">
                📤 Отправить ответы
            </button>

            <button type="button" onclick="window.location.reload()" style="background: #6c757d; margin-left: 10px;">
                🔄 Обновить страницу
            </button>
        </div>
    </form>
<?php endif; ?>

<hr style="margin: 40px 0;">

<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6;">
    <h3>🔧 Информация для отладки:</h3>
    <ul>
        <li>Текущая сессия: <?php echo session_id(); ?></li>
        <li>ID сессии опроса: <?php echo $sessionId; ?></li>
        <li>Время: <?php echo date('Y-m-d H:i:s'); ?></li>
        <li>Запросов к этой странице: <?php echo $_SESSION['visit_count'] = ($_SESSION['visit_count'] ?? 0) + 1; ?></li>
        <li><a href="javascript:void(0)" onclick="document.getElementById('debug-info').style.display='block'">Показать больше информации</a></li>
    </ul>

    <div id="debug-info" style="display: none; margin-top: 10px;">
        <h4>Детальная информация:</h4>
        <p><strong>SESSION:</strong> <?php echo htmlspecialchars(print_r($_SESSION, true)); ?></p>
        <p><strong>POST:</strong> <?php echo htmlspecialchars(print_r($_POST, true)); ?></p>
    </div>
</div>

</body>
</html>