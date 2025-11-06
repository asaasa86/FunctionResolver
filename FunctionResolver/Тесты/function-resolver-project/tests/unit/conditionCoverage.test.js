const {
    computeY, // Добавить импорт
    findLocalMinimumInDirection
} = require('../../src/js/functionResolver');

describe('Комбинаторное покрытие условий', () => {
    test('Все комбинации условий в findLocalMinimumInDirection', () => {
        const testCases = [
            { x0: -0.5, h: 0.1, direction: 1 },
            { x0: 1.5, h: 0.1, direction: -1 }
        ];
        
        testCases.forEach(testCase => {
            const result = findLocalMinimumInDirection(
                testCase.x0, 
                testCase.h, 
                testCase.direction
            );
            
            if (result.found) {
                // Проверяем, что условие минимума действительно выполняется
                const y0 = computeY(result.minX - testCase.h);
                const y1 = computeY(result.minX);
                const y2 = computeY(result.minX + testCase.h);
                
                expect(y0).toBeGreaterThan(y1);
                expect(y1).toBeLessThan(y2);
            }
        });
    });
});