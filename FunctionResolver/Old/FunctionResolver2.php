<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Нахождение локального минимума функции</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        canvas { max-width: 800px; margin: 20px 0; }
        .error { color: red; }
        .result { margin-top: 20px; }
        ul { list-style-type: none; padding: 0; }
        li { margin: 5px 0; }
    </style>
</head>
<body>
<h1>Нахождение локального минимума функции Y(X) = X² + 0.5 - sin(3X)</h1>
<form id="calcForm">
    <label for="x0">Начальное значение X₀:</label>
    <input type="number" step="any" id="x0" required><br><br>
    <label for="h">Шаг изменения H:</label>
    <input type="number" step="any" id="h" min="0.0001" required><br><br>
    <button type="submit">Вычислить</button>
</form>
<div id="errors" class="error"></div>
<div id="results" class="result" style="display: none;">
    <h2>Результаты</h2>
    <p>Y(X) = X² + 0.5 - sin(3X)</p>
    <p><strong>Введённый X₀:</strong> <span id="displayX0"></span></p>
    <p><strong>Введённый H:</strong> <span id="displayH"></span></p>
    <p id="minResult"></p>
    <canvas id="chart"></canvas>
    <h3>Вычисленные точки:</h3>
    <ul id="pointsList"></ul>
</div>

<script>
    const form = document.getElementById('calcForm');
    const errorsDiv = document.getElementById('errors');
    const resultsDiv = document.getElementById('results');
    const displayX0 = document.getElementById('displayX0');
    const displayH = document.getElementById('displayH');
    const minResult = document.getElementById('minResult');
    const pointsList = document.getElementById('pointsList');
    const chartCanvas = document.getElementById('chart');

    let chartInstance = null;

    function computeY(x) {
        return x * x + 0.5 - Math.sin(3 * x);
    }

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        errorsDiv.innerHTML = '';
        resultsDiv.style.display = 'none';

        const x0Input = parseFloat(document.getElementById('x0').value);
        const hInput = parseFloat(document.getElementById('h').value);

        if (isNaN(x0Input)) {
            errorsDiv.innerHTML = 'X₀ должно быть числом.';
            return;
        }
        if (isNaN(hInput) || hInput <= 0) {
            errorsDiv.innerHTML = 'H должно быть положительным числом.';
            return;
        }

        const x0 = x0Input;
        const h = hInput;
        const maxSteps = 1000;
        let found = false;
        let minX = null;
        let minY = null;
        let allPoints = [];

        const directions = ['right', 'left'];

        for (const dir of directions) {
            const stepSign = (dir === 'right') ? 1 : -1;
            let x = x0;
            let prevPrevY = computeY(x);
            let points = [{ x: x, y: prevPrevY }];

            x += stepSign * h;
            if (Math.abs(x - x0) >= maxSteps * h) continue;
            let prevY = computeY(x);
            points.push({ x: x, y: prevY });

            x += stepSign * h;
            if (Math.abs(x - x0) >= maxSteps * h) continue;
            let currentY = computeY(x);
            points.push({ x: x, y: currentY });

            x += stepSign * h;

            while (Math.abs(x - x0) < maxSteps * h) {
                let nextY = computeY(x);
                points.push({ x: x, y: nextY });

                if (prevPrevY > prevY && prevY < currentY) {
                    found = true;
                    minX = x - stepSign * h;
                    minY = prevY;
                    allPoints = points;
                    break;
                }

                prevPrevY = prevY;
                prevY = currentY;
                currentY = nextY;
                x += stepSign * h;
            }

            if (found) break;
            allPoints = points;
        }

        displayX0.textContent = x0.toFixed(4);
        displayH.textContent = h.toFixed(4);

        if (found) {
            minResult.innerHTML = `Локальный минимум найден в точке: X = ${minX.toFixed(4)}, Y = ${minY.toFixed(4)}`;
        } else {
            minResult.innerHTML = 'ОШИБКА: Локальный минимум не найден в пределах 1000 итераций. Увеличьте/уменьшите шаг H или начальное X.';
        }

        pointsList.innerHTML = '';
        allPoints.forEach(p => {
            const li = document.createElement('li');
            li.textContent = `X = ${p.x.toFixed(4)}, Y = ${p.y.toFixed(4)}`;
            pointsList.appendChild(li);
        });

        drawChart(allPoints, minX, minY);

        resultsDiv.style.display = 'block';
    });

    function drawChart(points, minX, minY) {
        const allX = points.map(p => p.x);
        const minXVal = Math.min(...allX);
        const maxXVal = Math.max(...allX);
        const range = maxXVal - minXVal;
        const extendedMin = minXVal - range * 0.5;
        const extendedMax = maxXVal + range * 0.5;

        const graphPoints = [];
        for (let x = extendedMin; x <= extendedMax; x += (extendedMax - extendedMin) / 200) {
            graphPoints.push({ x: x, y: computeY(x) });
        }

        if (chartInstance) {
            chartInstance.destroy();
        }

        const datasets = [{
            label: 'Y(X) = X² + 0.5 - sin(3X)',
            data: graphPoints,
            borderColor: 'blue',
            fill: false,
            pointRadius: 0,
            borderWidth: 2
        }, {
            label: 'Вычисленные точки',
            data: points,
            borderColor: 'red',
            backgroundColor: 'red',
            pointRadius: 3,
            showLine: false
        }];

        if (minX !== null) {
            datasets.push({
                label: 'Локальный минимум',
                data: [{ x: minX, y: minY }],
                borderColor: 'green',
                backgroundColor: 'green',
                pointRadius: 6,
                showLine: false
            });
        }

        chartInstance = new Chart(chartCanvas, {
            type: 'line',
            data: { datasets: datasets },
            options: {
                scales: {
                    x: {
                        type: 'linear',
                        position: 'bottom'
                    },
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
    }
</script>
</body>
</html>
