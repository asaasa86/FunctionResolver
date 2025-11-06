const { computeY } = require("../../src/js/functionResolver");
const {
    findLocalMinimumInDirection,
    findGlobalMinimum
} = require('../../src/js/functionResolver');

describe('ПОКРЫТИЕ РЕШЕНИЙ - Каждое решение должно принимать true и false', () => {
    
    test('Тест 1: Решение о нахождении минимума (true ветка)', () => {
        // Цель: проверить ветку когда минимум НАЙДЕН (found = true)
        // Условие: (y0 > y1 && y1 < y2) = true
        const result = findLocalMinimumInDirection(-0.5, 0.1, 1);
        expect(result.found).toBe(true);
        expect(result.minX).toBeDefined();
        expect(result.minY).toBeDefined();
    });

    test('Тест 2: Решение о ненахождении минимума (false ветка)', () => {
        // Цель: проверить ветку когда минимум НЕ НАЙДЕН (found = false)
        // Условие: (y0 > y1 && y1 < y2) = false (из-за большого шага)
        const result = findLocalMinimumInDirection(100, 100, 1);
        expect(result.found).toBe(false);
        expect(result.iterations).toBe(1000); // Достигнут лимит итераций
    });

    test('Тест 3: Решение в цикле while - выход по нахождению минимума', () => {
        // Цель: проверить выход из цикла при found = true
        const result = findLocalMinimumInDirection(0, 0.1, 1);
        expect(result.found).toBe(true);
        expect(result.iterations).toBeLessThan(1000);
    });

    test('Тест 4: Решение в цикле while - выход по достижению maxIterations', () => {
        // Цель: проверить выход из цикла при iterations >= maxIterations
        const result = findLocalMinimumInDirection(1000, 1, 1);
        expect(result.found).toBe(false);
        expect(result.iterations).toBe(1000);
    });

    test('Тест 5: Решение в findGlobalMinimum - обновление минимума', () => {
        // Цель: проверить ветку когда найден новый минимум (y < globalMinY) = true
        const result = findGlobalMinimum(5);
        // Проверяем, что действительно найден минимум
        const randomPoints = [-4, -2, 0, 2, 4];
        randomPoints.forEach(x => {
            expect(computeY(x)).toBeGreaterThanOrEqual(result.globalMinY);
        });
    });
});