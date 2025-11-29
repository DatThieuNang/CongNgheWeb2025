<?php
// Đọc toàn bộ file Quiz.txt thành mảng các dòng
$filename = __DIR__ . '/Quiz.txt';
if (!file_exists($filename)) {
    die('Không tìm thấy file Quiz.txt');
}

$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Parse các dòng thành danh sách câu hỏi
$questions = [];
$current = null;

foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '') {
        continue;
    }

    // Dòng ANSWER: X  => kết thúc 1 câu hỏi
    if (stripos($line, 'ANSWER:') === 0) {
        $answer = trim(substr($line, strlen('ANSWER:')));
        if ($current !== null) {
            $current['answer'] = strtoupper($answer); // A/B/C/D
            $questions[] = $current;
            $current = null;
        }
        continue;
    }

    // Nếu chưa có câu hỏi hiện tại => đây là dòng câu hỏi
    if ($current === null) {
        $current = [
            'question' => $line,
            'options'  => [],
            'answer'   => null
        ];
        continue;
    }

    // Nếu là dòng đáp án A./B./C./D.
    if (preg_match('/^([A-D])\.(.*)$/u', $line, $m)) {
        $label = strtoupper($m[1]);      // A/B/C/D
        $text  = trim($m[2]);
        $current['options'][$label] = $text;
    }
}

// Xử lý chấm điểm khi submit
$userAnswers = [];
$score = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correctCount = 0;
    foreach ($questions as $index => $q) {
        $name = 'q' . $index;
        $user = $_POST[$name] ?? null;
        $userAnswers[$index] = $user;
        if ($user !== null && strtoupper($user) === $q['answer']) {
            $correctCount++;
        }
    }
    $score = $correctCount . ' / ' . count($questions);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bài 02 - Trắc nghiệm </title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Bài 02: Trắc nghiệm </h1>

    <?php if (empty($questions)): ?>
        <p><b>Không đọc được câu hỏi nào từ file.</b></p>
    <?php else: ?>
        <form method="post">
            <?php foreach ($questions as $index => $q): ?>
                <div class="question">
                    <div class="question-title">
                        Câu <?php echo $index + 1; ?>:
                        <?php echo htmlspecialchars($q['question']); ?>
                    </div>
                    <div class="options">
                        <?php foreach ($q['options'] as $label => $text): 
                            $name = 'q' . $index;
                            $checked = (isset($userAnswers[$index]) && $userAnswers[$index] === $label)
                                        ? 'checked' : '';
                        ?>
                            <label class="option">
                                <input type="radio"
                                       name="<?php echo $name; ?>"
                                       value="<?php echo $label; ?>"
                                       <?php echo $checked; ?>>
                                <?php echo $label . '. ' . htmlspecialchars($text); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($score !== null): ?>
                        <?php
                            $user = $userAnswers[$index] ?? null;
                            $isCorrect = ($user !== null && strtoupper($user) === $q['answer']);
                        ?>
                        <div class="answer-key">
                            Bạn chọn:
                            <?php if ($user === null): ?>
                                <span class="wrong">Không chọn</span>
                            <?php elseif ($isCorrect): ?>
                                <span class="correct"><?php echo $user; ?></span>
                            <?php else: ?>
                                <span class="wrong"><?php echo $user; ?></span>
                            <?php endif; ?>
                            &nbsp;| Đáp án đúng: <b><?php echo $q['answer']; ?></b>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn-submit">Nộp bài</button>
        </form>

        <?php if ($score !== null): ?>
            <div class="result">
                Kết quả của bạn: <b><?php echo $score; ?></b>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>
