const {
    computeY,
    findLocalMinimumInDirection,
    findGlobalMinimum,
    findAllLocalMinima
} = require('../../src/js/functionResolver');

describe('ПОКРЫТИЕ ОПЕРАТОРОВ - Каждый оператор должен быть выполнен хотя бы один раз', () => {
    
    test('Тест 1: computeY выполняет все математические операторы', () => {
        // Цель: выполнить все операторы в функции computeY
        // Операторы: x*x, 0.5, Math.sin(3*x), сложение и вычитание
        const result = computeY(2);
        expect(typeof result).toBe('number');
        expect(result).toBeCloseTo(4.5 - Math.sin(6));
    });

    test('Тест 2: findLocalMinimumInDirection выполняет основные операторы цикла и условий', () => {
        // Цель: выполнить операторы инициализации, условия while, вычисления точек, условия if
        const result = findLocalMinimumInDirection(0, 0.1, 1);
        expect(result).toHaveProperty('found');
        expect(result).toHaveProperty('points');
        expect(result).toHaveProperty('iterations');
        // Проверяем, что были выполнены операторы внутри цикла
        expect(result.points.length).toBeGreaterThan(0);
    });

    test('Тест 3: findGlobalMinimum выполняет все операторы поиска минимума', () => {
        // Цель: выполнить операторы инициализации, цикла for, условия if
        const result = findGlobalMinimum(5);
        expect(result).toHaveProperty('globalMinX');
        expect(result).toHaveProperty('globalMinY');
        // Проверяем, что глобальный минимум корректен
        expect(result.globalMinY).toBeLessThanOrEqual(computeY(0));
    });

    test('Тест 4: findAllLocalMinima выполняет операторы поиска всех минимумов', () => {
        // Цель: выполнить все операторы поиска локальных минимумов
        const result = findAllLocalMinima(5);
        expect(Array.isArray(result)).toBe(true);
        // Проверяем, что каждый найденный минимум удовлетворяет условиям
        result.forEach(min => {
            expect(min).toHaveProperty('x');
            expect(min).toHaveProperty('y');
        });
    });
});