<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Нахождение локального минимума функции</title>
</head>
<body>
<h1>Нахождение локального минимума функции Y(X) = X^2 + 0.5 - sin(3*X)</h1>
<form method="POST" action="">
    <label for="x0">Начальное значение X0:</label>
    <input type="number" step="any" name="x0" id="x0" required><br><br>

    <label for="h">Шаг изменения H:</label>
    <input type="number" step="any" name="h" id="h" min="0.0001" required><br><br>

    <input type="submit" value="Вычислить">
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $x0_raw = filter_input(INPUT_POST, 'x0', FILTER_VALIDATE_FLOAT);
    $h_raw = filter_input(INPUT_POST, 'h', FILTER_VALIDATE_FLOAT);

    $errors = [];

    if ($x0_raw === false || $x0_raw === null) {
        $errors[] = "X₀ должно быть числом (не текст, не пустое).";
    }

    if ($h_raw === false || $h_raw === null) {
        $errors[] = "H должно быть числом (не текст, не пустое).";
    }

    if (!empty($errors)) {
        echo "<h2>Ошибки</h2><ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
        return;
    }

    $x0 = (float)$x0_raw;
    $h = (float)$h_raw;

    if ($h <= 0) {
        echo "<p>ОШИБКА: Шаг H должен быть положительным числом.</p>";
        return;
    }

    function computeY($x) {
        return $x * $x + 0.5 - sin(3 * $x);
    }

    $directions = ['right', 'left'];
    $found = false;
    $minX = null;
    $minY = null;
    $points = [];
    $maxSteps = 1000;

    foreach ($directions as $dir) {
        $stepSign = ($dir === 'right') ? 1 : -1;
        $x = $x0;
        $prevPrevY = computeY($x);
        $points = [['x' => $x, 'y' => $prevPrevY]];
        $x += $stepSign * $h;

        if (abs($x - $x0) < $maxSteps * $h) {
            $prevY = computeY($x);
            $points[] = ['x' => $x, 'y' => $prevY];
            $x += $stepSign * $h;
        } else {
            continue;
        }

        if (abs($x - $x0) < $maxSteps * $h) {
            $currentY = computeY($x);
            $points[] = ['x' => $x, 'y' => $currentY];
            $x += $stepSign * $h;
        } else {
            continue;
        }

        while (abs($x - $x0) < $maxSteps * $h) {
            $nextY = computeY($x);
            $points[] = ['x' => $x, 'y' => $nextY];

            if ($prevPrevY > $prevY && $prevY < $currentY) {
                $found = true;
                $minX = $x - $stepSign * $h;
                $minY = $prevY;
                break;
            }

            $prevPrevY = $prevY;
            $prevY = $currentY;
            $currentY = $nextY;
            $x += $stepSign * $h;
        }

        if ($found) break;
    }

    echo "<h2>Результаты</h2>";
    echo "<p>Y(X) = X^2 + 0.5 - sin(3*X)<p>";
    echo "<p><strong>Введённый X₀:</strong> " . number_format($x0, 4) . "</p>";
    echo "<p><strong>Введённый H:</strong> " . number_format($h, 4) . "</p>";

    if ($found) {
        echo "<p>Локальный минимум найден в точке: X = " . number_format($minX, 4) . ", Y = " . number_format($minY, 4) . "</p>";
    } else {
        echo "<p>ОШИБКА: Локальный минимум не найден в пределах 1000 итераций. Увеличьте/уменьшите шаг H или начальное X.</p>";
    }

    echo "<h3>Точки:</h3><ul>";
    foreach ($points as $p) {
        echo "<li>X = " . number_format($p['x'], 4) . ", Y = " . number_format($p['y'], 4) . "</li>";
    }
    echo "</ul>";
}
?>

</body>
</html>