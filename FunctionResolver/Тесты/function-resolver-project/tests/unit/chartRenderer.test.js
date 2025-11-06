// Мокаем зависимости
jest.mock('../../src/js/functionResolver', () => ({
    computeY: jest.fn().mockReturnValue(0.5)
}));

const { drawChart } = require('../../src/js/chartRenderer');

// Мокаем Chart.js и DOM
global.Chart = jest.fn().mockImplementation(() => ({
    destroy: jest.fn()
}));

global.document.getElementById = jest.fn().mockReturnValue({
    getContext: jest.fn().mockReturnValue({})
});

describe('Chart Renderer', () => {
    beforeEach(() => {
        Chart.mockClear();
        document.getElementById.mockClear();
    });

    test('should create chart with correct datasets', () => {
        const points = [{ x: 0, y: 0.5 }];
        drawChart(points, 0.5, 0.2, 0.5, 0.2, [], 0, 5);
        
        expect(Chart).toHaveBeenCalled();
    });
});