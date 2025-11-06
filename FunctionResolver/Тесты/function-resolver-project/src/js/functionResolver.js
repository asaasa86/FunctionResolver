// Только математические функции
function computeY(x) {
    return x * x + 0.5 - Math.sin(3 * x);
}

function findLocalMinimumInDirection(x0, h, direction) {
    const points = [];
    let x = x0;
    let found = false;
    let minX = null;
    let minY = null;
    let iterations = 0;
    const maxIterations = 1000;
    
    points.push({ x: x, y: computeY(x) });
    
    while (iterations < maxIterations && !found) {
        const x1 = x + direction * h;
        const x2 = x1 + direction * h;
        
        const y0 = computeY(x);
        const y1 = computeY(x1);
        const y2 = computeY(x2);
        
        points.push({ x: x1, y: y1 });
        points.push({ x: x2, y: y2 });
        
        if (y0 > y1 && y1 < y2) {
            found = true;
            minX = x1;
            minY = y1;
        }
        
        x = x1;
        iterations++;
    }
    
    return { found, minX, minY, points, iterations, direction };
}

function findGlobalMinimum(searchRange) {
    let globalMinX = 0;
    let globalMinY = computeY(0);
    const step = 0.01;
    const iterations = Math.floor(searchRange * 2 / step);
    
    for (let i = 0; i <= iterations; i++) {
        const x = -searchRange + i * step;
        const y = computeY(x);
        
        if (y < globalMinY) {
            globalMinY = y;
            globalMinX = x;
        }
    }
    
    return { globalMinX, globalMinY };
}

function findAllLocalMinima(searchRange) {
    const minima = [];
    const step = 0.01;
    const iterations = Math.floor(searchRange * 2 / step);
    
    for (let i = 1; i < iterations - 1; i++) {
        const x_prev = -searchRange + (i - 1) * step;
        const x_curr = -searchRange + i * step;
        const x_next = -searchRange + (i + 1) * step;
        
        const y_prev = computeY(x_prev);
        const y_curr = computeY(x_curr);
        const y_next = computeY(x_next);
        
        if (y_prev > y_curr && y_curr < y_next) {
            minima.push({ x: x_curr, y: y_curr });
        }
    }
    
    return minima;
}

// Экспорт для тестов
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        computeY,
        findLocalMinimumInDirection,
        findGlobalMinimum,
        findAllLocalMinima
    };
}