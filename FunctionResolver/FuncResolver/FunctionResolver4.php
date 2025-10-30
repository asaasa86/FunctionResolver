<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Нахождение локального и глобального минимума функции</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        canvas { max-width: 800px; margin: 20px 0; }
        .error { color: red; }
        .result { margin-top: 20px; }
        ul { list-style-type: none; padding: 0; }
        li { margin: 5px 0; }
        .verification { background-color: #f0f8ff; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .direction { color: #666; font-style: italic; }
        .comparison { background-color: #f9f9f9; padding: 10px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #4CAF50; }
    </style>
</head>
<body>
<h1>Нахождение локального и глобального минимума функции Y(X) = X² + 0.5 - sin(3X)</h1>
<form id="calcForm">
    <label for="x0">Начальное значение X₀:</label>
    <input type="number" step="any" id="x0" required><br><br>
    <label for="h">Шаг изменения H:</label>
    <input type="number" step="any" id="h" min="0.0001" required><br><br>
    <label for="searchRange">Диапазон поиска глобального минимума:</label>
    <input type="number" step="any" id="searchRange" value="5" min="1" max="20"><br><br>
    <button type="submit">Вычислить</button>
</form>
<div id="errors" class="error"></div>
<div id="results" class="result" style="display: none;">
    <h2>Результаты</h2>
    <p>Y(X) = X² + 0.5 - sin(3X)</p>
    <p><strong>Введённый X₀:</strong> <span id="displayX0"></span></p>
    <p><strong>Введённый H:</strong> <span id="displayH"></span></p>
    <p><strong>Диапазон поиска глобального минимума:</strong> ±<span id="displayRange"></span></p>
    
    <div class="comparison">
        <h3>Сравнение минимумов</h3>
        <p id="localMinResult"></p>
        <p id="globalMinResult"></p>
        <p id="comparisonResult"></p>
    </div>
    
    <div id="verification" class="verification"></div>
    <p id="directionInfo" class="direction"></p>
    <canvas id="chart"></canvas>
    <h3>Вычисленные точки (первые 20):</h3>
    <ul id="pointsList"></ul>
</div>

<script>
    const form = document.getElementById('calcForm');
    const errorsDiv = document.getElementById('errors');
    const resultsDiv = document.getElementById('results');
    const displayX0 = document.getElementById('displayX0');
    const displayH = document.getElementById('displayH');
    const displayRange = document.getElementById('displayRange');
    const localMinResult = document.getElementById('localMinResult');
    const globalMinResult = document.getElementById('globalMinResult');
    const comparisonResult = document.getElementById('comparisonResult');
    const pointsList = document.getElementById('pointsList');
    const chartCanvas = document.getElementById('chart');
    const verificationDiv = document.getElementById('verification');
    const directionInfo = document.getElementById('directionInfo');

    let chartInstance = null;

    function computeY(x) {
        return x * x + 0.5 - Math.sin(3 * x);
    }

    function findLocalMinimumInDirection(x0, h, direction) {
        const points = [];
        let x = x0;
        let found = false;
        let minX = null;
        let minY = null;
        let iterations = 0;
        const maxIterations = 1000;
        
        // Собираем начальные точки
        points.push({ x: x, y: computeY(x) });
        
        while (iterations < maxIterations && !found) {
            // Вычисляем новые точки
            const x1 = x + direction * h;
            const x2 = x1 + direction * h;
            
            const y0 = computeY(x);
            const y1 = computeY(x1);
            const y2 = computeY(x2);
            
            points.push({ x: x1, y: y1 });
            points.push({ x: x2, y: y2 });
            
            // Проверяем условие локального минимума: y0 > y1 и y1 < y2
            if (y0 > y1 && y1 < y2) {
                found = true;
                minX = x1;
                minY = y1;
            }
            
            // Переходим к следующей точке
            x = x1;
            iterations++;
        }
        
        return { found, minX, minY, points, iterations, direction };
    }

    function findGlobalMinimum(searchRange) {
        let globalMinX = 0;
        let globalMinY = computeY(0);
        const step = 0.01; // Мелкий шаг для точного поиска
        const iterations = Math.floor(searchRange * 2 / step);
        
        // Поиск минимума в диапазоне [-searchRange, searchRange]
        for (let i = 0; i <= iterations; i++) {
            const x = -searchRange + i * step;
            const y = computeY(x);
            
            if (y < globalMinY) {
                globalMinY = y;
                globalMinX = x;
            }
        }
        
        return { globalMinX, globalMinY };
    }

    function findAllLocalMinima(searchRange) {
        const minima = [];
        const step = 0.01;
        const iterations = Math.floor(searchRange * 2 / step);
        
        // Ищем все локальные минимумы в диапазоне
        for (let i = 1; i < iterations - 1; i++) {
            const x_prev = -searchRange + (i - 1) * step;
            const x_curr = -searchRange + i * step;
            const x_next = -searchRange + (i + 1) * step;
            
            const y_prev = computeY(x_prev);
            const y_curr = computeY(x_curr);
            const y_next = computeY(x_next);
            
            // Условие локального минимума
            if (y_prev > y_curr && y_curr < y_next) {
                minima.push({ x: x_curr, y: y_curr });
            }
        }
        
        return minima;
    }

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        errorsDiv.innerHTML = '';
        resultsDiv.style.display = 'none';
        verificationDiv.innerHTML = '';
        directionInfo.innerHTML = '';

        const x0Input = parseFloat(document.getElementById('x0').value);
        const hInput = parseFloat(document.getElementById('h').value);
        const searchRangeInput = parseFloat(document.getElementById('searchRange').value);

        // Валидация входных данных
        if (isNaN(x0Input)) {
            errorsDiv.innerHTML = 'ОШИБКА: X₀ должно быть числом.';
            return;
        }
        if (isNaN(hInput) || hInput <= 0) {
            errorsDiv.innerHTML = 'ОШИБКА: H должно быть положительным числом.';
            return;
        }
        if (isNaN(searchRangeInput) || searchRangeInput <= 0) {
            errorsDiv.innerHTML = 'ОШИБКА: Диапазон поиска должен быть положительным числом.';
            return;
        }

        const x0 = x0Input;
        const h = hInput;
        const searchRange = searchRangeInput;
        
        let found = false;
        let localMinX = null;
        let localMinY = null;
        let allPoints = [];
        let searchDirection = '';
        let iterations = 0;

        // Поиск локального минимума в обе стороны
        const rightResult = findLocalMinimumInDirection(x0, h, 1);  // Вправо
        const leftResult = findLocalMinimumInDirection(x0, h, -1); // Влево

        // Выбираем ближайший минимум
        if (rightResult.found && leftResult.found) {
            const rightDistance = Math.abs(rightResult.minX - x0);
            const leftDistance = Math.abs(leftResult.minX - x0);
            
            if (rightDistance < leftDistance) {
                found = true;
                localMinX = rightResult.minX;
                localMinY = rightResult.minY;
                allPoints = rightResult.points;
                searchDirection = 'вправо';
                iterations = rightResult.iterations;
            } else {
                found = true;
                localMinX = leftResult.minX;
                localMinY = leftResult.minY;
                allPoints = leftResult.points;
                searchDirection = 'влево';
                iterations = leftResult.iterations;
            }
        } else if (rightResult.found) {
            found = true;
            localMinX = rightResult.minX;
            localMinY = rightResult.minY;
            allPoints = rightResult.points;
            searchDirection = 'вправо';
            iterations = rightResult.iterations;
        } else if (leftResult.found) {
            found = true;
            localMinX = leftResult.minX;
            localMinY = leftResult.minY;
            allPoints = leftResult.points;
            searchDirection = 'влево';
            iterations = leftResult.iterations;
        }

        // Поиск глобального минимума
        const { globalMinX, globalMinY } = findGlobalMinimum(searchRange);
        
        // Поиск всех локальных минимумов для визуализации
        const allLocalMinima = findAllLocalMinima(searchRange);

        displayX0.textContent = x0.toFixed(4);
        displayH.textContent = h.toFixed(4);
        displayRange.textContent = searchRange.toFixed(1);

        if (found) {
            localMinResult.innerHTML = `<strong>Локальный минимум:</strong> X = ${localMinX.toFixed(4)}, Y = ${localMinY.toFixed(4)}`;
            globalMinResult.innerHTML = `<strong>Глобальный минимум в диапазоне ±${searchRange}:</strong> X = ${globalMinX.toFixed(4)}, Y = ${globalMinY.toFixed(4)}`;
            
            // Сравнение
            if (Math.abs(localMinY - globalMinY) < 0.001) {
                comparisonResult.innerHTML = `<strong>Найденный локальный минимум ЯВЛЯЕТСЯ глобальным минимумом в заданном диапазоне!</strong>`;
            } else {
                comparisonResult.innerHTML = `<strong>Найденный локальный минимум НЕ является глобальным минимумом в заданном диапазоне.</strong>`;
            }
            
            directionInfo.innerHTML = `Локальный минимум найден при движении ${searchDirection} от X₀ за ${iterations} итераций`;
            
            // Верификация минимума
            const leftY = computeY(localMinX - h);
            const rightY = computeY(localMinX + h);
            verificationDiv.innerHTML = `
                <strong>Проверка локального минимума:</strong><br>
                - Y(${(localMinX - h).toFixed(4)}) = ${leftY.toFixed(4)} > Y(${localMinX.toFixed(4)}) = ${localMinY.toFixed(4)}<br>
                - Y(${localMinX.toFixed(4)}) = ${localMinY.toFixed(4)} < Y(${(localMinX + h).toFixed(4)}) = ${rightY.toFixed(4)}<br>
                - Условие локального минимума выполняется: соседние значения больше
            `;
        } else {
            localMinResult.innerHTML = '<strong>Локальный минимум:</strong> не найден в пределах 1000 итераций в обе стороны.';
            globalMinResult.innerHTML = `<strong>Глобальный минимум в диапазоне ±${searchRange}:</strong> X = ${globalMinX.toFixed(4)}, Y = ${globalMinY.toFixed(4)}`;
            comparisonResult.innerHTML = 'Невозможно сравнить, так как локальный минимум не найден.';
            
            // Объединяем точки из обоих направлений для отображения
            allPoints = [...leftResult.points.slice().reverse(), ...rightResult.points.slice(1)];
        }

        // Отображаем точки (ограничиваем количество для читаемости)
        pointsList.innerHTML = '';
        const displayPoints = allPoints.slice(0, 20);
        displayPoints.forEach(p => {
            const li = document.createElement('li');
            li.textContent = `X = ${p.x.toFixed(4)}, Y = ${p.y.toFixed(4)}`;
            pointsList.appendChild(li);
        });

        if (allPoints.length > 20) {
            const li = document.createElement('li');
            li.textContent = `... и еще ${allPoints.length - 20} точек`;
            pointsList.appendChild(li);
        }

        drawChart(allPoints, localMinX, localMinY, globalMinX, globalMinY, allLocalMinima, x0, searchRange);
        resultsDiv.style.display = 'block';
    });

    function drawChart(points, localMinX, localMinY, globalMinX, globalMinY, allLocalMinima, x0, searchRange) {
        if (points.length === 0) return;

        // Определяем диапазон для графика
        const chartMinX = -searchRange;
        const chartMaxX = searchRange;

        // Создаем плавный график функции
        const graphPoints = [];
        const step = (chartMaxX - chartMinX) / 300;
        for (let x = chartMinX; x <= chartMaxX; x += step) {
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
            borderWidth: 2,
            tension: 0.4
        }];

        // Добавляем вычисленные точки
        if (points.length > 0) {
            datasets.push({
                label: 'Вычисленные точки',
                data: points,
                borderColor: 'red',
                backgroundColor: 'red',
                pointRadius: 4,
                showLine: false
            });
        }

        // Добавляем начальную точку
        datasets.push({
            label: 'Начальная точка X₀',
            data: [{ x: x0, y: computeY(x0) }],
            borderColor: 'orange',
            backgroundColor: 'orange',
            pointRadius: 6,
            showLine: false
        });

        // Добавляем все локальные минимумы
        if (allLocalMinima.length > 0) {
            datasets.push({
                label: 'Все локальные минимумы',
                data: allLocalMinima,
                borderColor: 'purple',
                backgroundColor: 'purple',
                pointRadius: 4,
                showLine: false
            });
        }

        // Добавляем найденный локальный минимум
        if (localMinX !== null) {
            datasets.push({
                label: 'Найденный локальный минимум',
                data: [{ x: localMinX, y: localMinY }],
                borderColor: 'green',
                backgroundColor: 'green',
                pointRadius: 8,
                pointStyle: 'circle',
                showLine: false
            });
        }

        // Добавляем глобальный минимум
        datasets.push({
            label: 'Глобальный минимум',
            data: [{ x: globalMinX, y: globalMinY }],
            borderColor: 'gold',
            backgroundColor: 'gold',
            pointRadius: 10,
            pointStyle: 'star',
            showLine: false
        });

        chartInstance = new Chart(chartCanvas, {
            type: 'line',
            data: { datasets: datasets },
            options: {
                responsive: true,
                scales: {
                    x: {
                        type: 'linear',
                        position: 'bottom',
                        title: {
                            display: true,
                            text: 'X'
                        },
                        min: chartMinX,
                        max: chartMaxX
                    },
                    y: {
                        beginAtZero: false,
                        title: {
                            display: true,
                            text: 'Y(X)'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `X: ${context.parsed.x.toFixed(4)}, Y: ${context.parsed.y.toFixed(4)}`;
                            }
                        }
                    }
                }
            }
        });
    }
</script>
</body>
</html>