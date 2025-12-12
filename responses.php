<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';
?>

<h1>Все ответы</h1>
<?php
$stmt = $pdo->query("
    select q.text as question, r.answer_text, r.session_id, r.created_at
    from responses r
    join questions q ON r.questions_id = q.id
    order by r.created_at desc
");
$all_answers = $stmt->fetchAll();

foreach ($all_answers as $row) {
    echo "<div style='border:1px solid #ccc; margin:10px 0; padding:10px;'>";
    echo "<b>Сессия:</b> " . substr($row['session_id'], 0) . "<br>";
    echo "<b>Вопрос:</b> " . htmlspecialchars($row['question']) . "<br>";
    echo "<b>Ответ:</b> " . htmlspecialchars($row['answer_text']). "<br>";
    echo "<b>Время:</b>" . htmlspecialchars($row['created_at']) . "<br>";
    echo "</div>";

}
?>

<br>