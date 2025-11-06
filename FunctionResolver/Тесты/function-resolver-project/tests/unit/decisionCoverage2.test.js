const {
    computeY,
    findLocalMinimumInDirection
} = require('../../src/js/functionResolver');

describe('ПОКРЫТИЕ УСЛОВИЙ - Каждое элементарное условие должно принимать true и false', () => {
    
    test('Тест 1: Условие iterations < maxIterations = true, !found = true', () => {
        // Цель: войти в цикл (оба условия true)
        const result = findLocalMinimumInDirection(0, 0.1, 1);
        expect(result.iterations).toBeLessThan(1000);
        expect(result.found).toBe(true);
    });

    test('Тест 2: Условие iterations < maxIterations = false, !found = true', () => {
        // Цель: не войти в цикл (первое условие false)
        const result = findLocalMinimumInDirection(1000, 1, 1);
        expect(result.iterations).toBe(1000);
        expect(result.found).toBe(false);
    });

    test('Тест 3: Условие y0 > y1 = true, y1 < y2 = true (нахождение минимума)', () => {
        // Цель: проверить условие минимума (оба условия true)
        const result = findLocalMinimumInDirection(-0.5, 0.1, 1);
        if (result.found) {
            const y0 = computeY(result.minX - 0.1);
            const y1 = computeY(result.minX);
            const y2 = computeY(result.minX + 0.1);
            expect(y0).toBeGreaterThan(y1);
            expect(y1).toBeLessThan(y2);
        }
    });

    test('Тест 4: Условие валидации - корректные данные', () => {
        // Цель: проверить условия валидации (все условия false - нет ошибок)
        const { validateInputs } = require('../../src/js/validationHelpers');
        expect(() => validateInputs(0, 0.1, 5)).not.toThrow();
    });

    test('Тест 5: Условие валидации - некорректный X0', () => {
        // Цель: isNaN(x0) = true
        const { validateInputs } = require('../../src/js/validationHelpers');
        expect(() => validateInputs(NaN, 0.1, 5)).toThrow('X₀ должно быть числом');
    });

    test('Тест 6: Условие валидации - некорректный H', () => {
        // Цель: isNaN(h) || h <= 0 = true
        const { validateInputs } = require('../../src/js/validationHelpers');
        expect(() => validateInputs(0, 0, 5)).toThrow('H должно быть положительным числом');
        expect(() => validateInputs(0, -1, 5)).toThrow('H должно быть положительным числом');
    });
});