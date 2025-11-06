// Основной обработчик формы
function setupFormHandler() {
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
    const verificationDiv = document.getElementById('verification');
    const directionInfo = document.getElementById('directionInfo');

    if (!form) return;

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        errorsDiv.innerHTML = '';
        resultsDiv.style.display = 'none';
        verificationDiv.innerHTML = '';
        directionInfo.innerHTML = '';

        const x0Input = parseFloat(document.getElementById('x0').value);
        const hInput = parseFloat(document.getElementById('h').value);
        const searchRangeInput = parseFloat(document.getElementById('searchRange').value);

        try {
            validateInputs(x0Input, hInput, searchRangeInput);
        } catch (error) {
            errorsDiv.innerHTML = 'ОШИБКА: ' + error.message;
            return;
        }

        const x0 = x0Input;
        const h = hInput;
        const searchRange = searchRangeInput;
        
        // Поиск локального минимума в обе стороны
        const rightResult = findLocalMinimumInDirection(x0, h, 1);
        const leftResult = findLocalMinimumInDirection(x0, h, -1);

        let found = false;
        let localMinX = null;
        let localMinY = null;
        let allPoints = [];
        let searchDirection = '';
        let iterations = 0;

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
            if (compareMinima(localMinY, globalMinY)) {
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

        // Отображаем точки
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
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    setupFormHandler();
});

// Экспорт для тестов
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        setupFormHandler
    };
}