// Вспомогательные функции для валидации и логики
function validateInputs(x0, h, searchRange) {
    if (isNaN(x0)) {
        throw new Error('X₀ должно быть числом');
    }
    if (isNaN(h) || h <= 0) {
        throw new Error('H должно быть положительным числом');
    }
    if (isNaN(searchRange) || searchRange <= 0) {
        throw new Error('Диапазон поиска должен быть положительным числом');
    }
}

function selectSearchDirection(rightResult, leftResult, x0) {
    if (rightResult.found && leftResult.found) {
        const rightDistance = Math.abs(rightResult.minX - x0);
        const leftDistance = Math.abs(leftResult.minX - x0);
        return rightDistance < leftDistance ? 'вправо' : 'влево';
    } else if (rightResult.found) {
        return 'вправо';
    } else if (leftResult.found) {
        return 'влево';
    }
    return undefined;
}

function compareMinima(localY, globalY, tolerance = 0.001) {
    return Math.abs(localY - globalY) < tolerance;
}

// Экспорт для тестов
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        validateInputs,
        selectSearchDirection,
        compareMinima
    };
}