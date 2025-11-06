const {
    computeY,
    findLocalMinimumInDirection,
    findGlobalMinimum
} = require('../../src/js/functionResolver');

const {
    validateInputs,
    selectSearchDirection,
    compareMinima
} = require('../../src/js/validationHelpers');

describe('ИНТЕГРАЦИОННЫЕ ТЕСТЫ - Проверка полного workflow приложения', () => {
    
    test('Тест 1: Полный сценарий с валидными данными', () => {
        // Шаг 1: Валидация входных данных
        expect(() => validateInputs(0, 0.1, 5)).not.toThrow();
        
        // Шаг 2: Поиск в обе стороны
        const rightResult = findLocalMinimumInDirection(0, 0.1, 1);
        const leftResult = findLocalMinimumInDirection(0, 0.1, -1);
        
        // Шаг 3: Выбор направления
        const direction = selectSearchDirection(rightResult, leftResult, 0);
        expect(['вправо', 'влево']).toContain(direction);
        
        // Шаг 4: Поиск глобального минимума
        const globalResult = findGlobalMinimum(5);
        expect(globalResult.globalMinY).toBeLessThan(computeY(0));
        
        // Шаг 5: Сравнение минимумов
        const localMin = rightResult.found ? rightResult : leftResult;
        const isGlobal = compareMinima(localMin.minY, globalResult.globalMinY);
        expect(typeof isGlobal).toBe('boolean');
    });

    test('Тест 2: Обработка граничных случаев', () => {
        // Очень маленький шаг
        const smallStepResult = findLocalMinimumInDirection(0, 0.00001, 1);
        expect(smallStepResult.iterations).toBeLessThanOrEqual(1000);
        
        // Очень большой начальный X
        const largeXResult = findLocalMinimumInDirection(1000, 0.1, 1);
        expect(largeXResult.iterations).toBeLessThanOrEqual(1000);
        
        // Нулевой случай
        const zeroResult = findLocalMinimumInDirection(0, 0.1, 1);
        expect(zeroResult).toBeDefined();
    });

    test('Тест 3: Сценарий с ошибками валидации', () => {
        // Некорректный X0
        expect(() => validateInputs('abc', 0.1, 5)).toThrow();
        
        // Отрицательный шаг
        expect(() => validateInputs(0, -0.1, 5)).toThrow();
        
        // Нулевой диапазон
        expect(() => validateInputs(0, 0.1, 0)).toThrow();
    });
});