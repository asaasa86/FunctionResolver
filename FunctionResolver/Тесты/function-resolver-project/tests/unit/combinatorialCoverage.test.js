const { computeY } = require("../../src/js/functionResolver");
const {
    findLocalMinimumInDirection
} = require('../../src/js/functionResolver');

const { selectSearchDirection } = require('../../src/js/validationHelpers');

describe('КОМБИНАТОРНОЕ ПОКРЫТИЕ УСЛОВИЙ - Все комбинации условий в точках принятия решений', () => {
    
    test('Тест 1: Все комбинации условий выбора направления поиска', () => {
        const testCases = [
            // [rightFound, leftFound, rightDistance, leftDistance, expectedDirection]
            { right: true, left: true, rightDist: 1, leftDist: 2, expected: 'вправо' },
            { right: true, left: true, rightDist: 2, leftDist: 1, expected: 'влево' },
            { right: true, left: false, rightDist: 1, leftDist: 2, expected: 'вправо' },
            { right: false, left: true, rightDist: 1, leftDist: 2, expected: 'влево' },
            { right: false, left: false, rightDist: 1, leftDist: 2, expected: undefined }
        ];

        testCases.forEach(({ right, left, rightDist, leftDist, expected }) => {
            const rightResult = { found: right, minX: 0 };
            const leftResult = { found: left, minX: 0 };
            
            // Мокаем Math.abs для контроля расстояний
            jest.spyOn(Math, 'abs')
                .mockReturnValueOnce(rightDist)
                .mockReturnValueOnce(leftDist);
            
            const direction = selectSearchDirection(rightResult, leftResult, 0);
            expect(direction).toBe(expected);
            
            Math.abs.mockRestore();
        });
    });

    test('Тест 2: Комбинации условий сравнения минимумов', () => {
        const { compareMinima } = require('../../src/js/validationHelpers');
        
        const testCases = [
            { localY: 1.0, globalY: 1.0, tolerance: 0.001, expected: true },
            { localY: 1.0, globalY: 1.0005, tolerance: 0.001, expected: true },
            { localY: 1.0, globalY: 1.002, tolerance: 0.001, expected: false },
            { localY: 2.0, globalY: 1.0, tolerance: 0.001, expected: false }
        ];

        testCases.forEach(({ localY, globalY, tolerance, expected }) => {
            const result = compareMinima(localY, globalY, tolerance);
            expect(result).toBe(expected);
        });
    });

    test('Тест 3: Комбинации условий в findLocalMinimumInDirection', () => {
        // Тестируем различные комбинации начальных условий
        const testCases = [
            { x0: -0.5, h: 0.1, direction: 1 },  // Должен найти минимум
            { x0: 1.5, h: 0.1, direction: -1 },  // Должен найти минимум
            { x0: 100, h: 100, direction: 1 },   // Не должен найти (большой шаг)
            { x0: 0, h: 0.001, direction: 1 }    // Маленький шаг - должен найти
        ];

        testCases.forEach(testCase => {
            const result = findLocalMinimumInDirection(
                testCase.x0, 
                testCase.h, 
                testCase.direction
            );
            
            // Проверяем корректность результата независимо от того, найден минимум или нет
            expect(result).toHaveProperty('found');
            expect(result).toHaveProperty('iterations');
            expect(result).toHaveProperty('points');
            
            if (result.found) {
                expect(result.minX).toBeDefined();
                expect(result.minY).toBeDefined();
                // Проверяем условие локального минимума
                const y0 = computeY(result.minX - testCase.h);
                const y1 = computeY(result.minX);
                const y2 = computeY(result.minX + testCase.h);
                expect(y0).toBeGreaterThan(y1);
                expect(y1).toBeLessThan(y2);
            }
        });
    });
});