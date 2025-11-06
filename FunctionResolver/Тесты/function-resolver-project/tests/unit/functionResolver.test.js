const {
    computeY,
    findLocalMinimumInDirection,
    findGlobalMinimum,
    findAllLocalMinima
} = require('../../src/js/functionResolver');

describe('Function Resolver - Покрытие операторов', () => {
    test('computeY выполняет все операторы', () => {
        const result = computeY(2);
        expect(typeof result).toBe('number');
    });

    test('findLocalMinimumInDirection выполняет основные операторы', () => {
        const result = findLocalMinimumInDirection(0, 0.1, 1);
        expect(result).toHaveProperty('found');
        expect(result).toHaveProperty('points');
    });
});

describe('Function Resolver - Покрытие решений', () => {
    test('Решение о нахождении минимума (true)', () => {
        const result = findLocalMinimumInDirection(-0.5, 0.1, 1);
        expect(result.found).toBeDefined();
    });

    test('Решение о ненахождении минимума (false)', () => {
        const result = findLocalMinimumInDirection(100, 100, 1);
        expect(result.found).toBe(false);
    });
});