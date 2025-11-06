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

describe('Интеграционные тесты полного workflow', () => {
    test('Полный сценарий с валидными данными', () => {
        // 1. Валидация
        expect(() => validateInputs(0, 0.1, 5)).not.toThrow();
        
        // 2. Поиск в обе стороны
        const rightResult = findLocalMinimumInDirection(0, 0.1, 1);
        const leftResult = findLocalMinimumInDirection(0, 0.1, -1);
        
        // 3. Выбор направления
        const direction = selectSearchDirection(rightResult, leftResult, 0);
        expect(['вправо', 'влево']).toContain(direction);
        
        // 4. Поиск глобального минимума
        const globalResult = findGlobalMinimum(5);
        expect(globalResult.globalMinY).toBeLessThan(computeY(0));
        
        // 5. Сравнение минимумов
        const localMin = rightResult.found ? rightResult : leftResult;
        const isGlobal = compareMinima(localMin.minY, globalResult.globalMinY);
        expect(typeof isGlobal).toBe('boolean');
    });
});