const {
    validateInputs,
    selectSearchDirection,
    compareMinima
} = require('../../src/js/validationHelpers');

describe('Validation Helpers - Покрытие условий', () => {
    test('Условия валидации входных данных', () => {
        expect(() => validateInputs(NaN, 0.1, 5)).toThrow();
        expect(() => validateInputs(0, 0, 5)).toThrow();
        expect(() => validateInputs(0, 0.1, -1)).toThrow();
    });

    test('Условия выбора направления', () => {
        const rightResult = { found: true, minX: 1 };
        const leftResult = { found: true, minX: -2 };
        
        const direction = selectSearchDirection(rightResult, leftResult, 0);
        expect(direction).toBe('вправо');
    });
});