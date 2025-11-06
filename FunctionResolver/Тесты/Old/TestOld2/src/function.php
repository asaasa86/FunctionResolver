<?php

class FunctionOptimizer
{
    /**
     * Целевая функция: f(x) = x² + 0.5 - sin(3x)
     */
    public static function f(float $x): float
    {
        return pow($x, 2) + 0.5 - sin(3 * $x);
    }

    /**
     * Находит локальный минимум функции выбранным методом
     */
    public static function findLocalMinimum(
        float $a = -2, 
        float $b = 2, 
        string $method = 'golden_section',
        float $tolerance = 1e-6,
        int $numPoints = 1000,
        int $maxIterations = 1000
    ): array {
        // Валидация входных данных
        if ($a >= $b) {
            return [
                'success' => false,
                'error' => 'Недопустимые границы интервала: a должно быть меньше b',
                'input' => ['a' => $a, 'b' => $b]
            ];
        }

        if ($tolerance <= 0) {
            return [
                'success' => false,
                'error' => 'Недопустимая точность: значение должно быть положительным',
                'input' => ['tolerance' => $tolerance]
            ];
        }

        if ($numPoints <= 0) {
            return [
                'success' => false,
                'error' => 'Недопустимое количество точек: значение должно быть положительным',
                'input' => ['numPoints' => $numPoints]
            ];
        }

        // Выбор метода оптимизации
        switch ($method) {
            case 'golden_section':
                return self::goldenSectionSearch($a, $b, $tolerance, $maxIterations);
            
            case 'uniform_search':
                return self::uniformSearch($a, $b, $numPoints);
            
            case 'random_search':
                return self::randomSearch($a, $b, $numPoints);
            
            case 'adaptive_search':
                return self::adaptiveSearch($a, $b, $numPoints, $tolerance);
            
            default:
                return [
                    'success' => false,
                    'error' => "Неизвестный метод оптимизации: '{$method}'. Доступные методы: golden_section, uniform_search, random_search, adaptive_search",
                    'input' => ['method' => $method]
                ];
        }
    }

    /**
     * Метод золотого сечения
     */
    private static function goldenSectionSearch(float $a, float $b, float $tol, int $maxIter): array
    {
        $goldenRatio = (1 + sqrt(5)) / 2;
        $c = $b - ($b - $a) / $goldenRatio;
        $d = $a + ($b - $a) / $goldenRatio;
        
        $iterations = 0;
        $functionCalls = 2; // Уже вычислили f(c) и f(d)
        
        while (abs($c - $d) > $tol && $iterations < $maxIter) {
            if (self::f($c) < self::f($d)) {
                $b = $d;
            } else {
                $a = $c;
            }
            
            $c = $b - ($b - $a) / $goldenRatio;
            $d = $a + ($b - $a) / $goldenRatio;
            $iterations++;
            $functionCalls += 2;
        }
        
        $x_min = ($b + $a) / 2;
        $f_min = self::f($x_min);
        $functionCalls++;
        
        return [
            'success' => true,
            'x' => $x_min,
            'fun' => $f_min,
            'iterations' => $iterations,
            'function_calls' => $functionCalls,
            'method' => 'golden_section',
            'precision' => abs($c - $d)
        ];
    }

    /**
     * Равномерный поиск (равномерная сетка)
     */
    private static function uniformSearch(float $a, float $b, int $numPoints): array
    {
        $step = ($b - $a) / $numPoints;
        $x_min = $a;
        $f_min = self::f($a);
        $functionCalls = 1;
        
        for ($i = 1; $i <= $numPoints; $i++) {
            $x = $a + $i * $step;
            $f_x = self::f($x);
            $functionCalls++;
            
            if ($f_x < $f_min) {
                $f_min = $f_x;
                $x_min = $x;
            }
        }
        
        return [
            'success' => true,
            'x' => $x_min,
            'fun' => $f_min,
            'points_evaluated' => $numPoints + 1,
            'function_calls' => $functionCalls,
            'method' => 'uniform_search',
            'step_size' => $step
        ];
    }

    /**
     * Случайный поиск (равномерное распределение)
     */
    private static function randomSearch(float $a, float $b, int $numPoints): array
    {
        $x_min = $a;
        $f_min = self::f($a);
        $functionCalls = 1;
        
        for ($i = 0; $i < $numPoints; $i++) {
            $x = $a + ($b - $a) * (mt_rand() / mt_getrandmax());
            $f_x = self::f($x);
            $functionCalls++;
            
            if ($f_x < $f_min) {
                $f_min = $f_x;
                $x_min = $x;
            }
        }
        
        return [
            'success' => true,
            'x' => $x_min,
            'fun' => $f_min,
            'points_evaluated' => $numPoints + 1,
            'function_calls' => $functionCalls,
            'method' => 'random_search',
            'search_range' => [$a, $b]
        ];
    }

    /**
     * Адаптивный поиск (двухэтапный)
     */
    private static function adaptiveSearch(float $a, float $b, int $numPoints, float $tol): array
    {
        // Первый этап: грубый равномерный поиск
        $coarsePoints = (int)($numPoints * 0.3);
        $coarseStep = ($b - $a) / $coarsePoints;
        
        $bestX = $a;
        $bestF = self::f($a);
        $functionCalls = 1;
        
        // Грубый поиск
        for ($i = 1; $i <= $coarsePoints; $i++) {
            $x = $a + $i * $coarseStep;
            $f_x = self::f($x);
            $functionCalls++;
            
            if ($f_x < $bestF) {
                $bestF = $f_x;
                $bestX = $x;
            }
        }
        
        // Второй этап: точный поиск вокруг лучшей точки
        $localRange = max(($b - $a) * 0.1, $tol * 10);
        $localA = max($a, $bestX - $localRange);
        $localB = min($b, $bestX + $localRange);
        
        $finePoints = $numPoints - $coarsePoints;
        $fineStep = ($localB - $localA) / $finePoints;
        
        // Точный поиск
        for ($i = 0; $i <= $finePoints; $i++) {
            $x = $localA + $i * $fineStep;
            $f_x = self::f($x);
            $functionCalls++;
            
            if ($f_x < $bestF) {
                $bestF = $f_x;
                $bestX = $x;
            }
        }
        
        return [
            'success' => true,
            'x' => $bestX,
            'fun' => $bestF,
            'points_evaluated' => $numPoints + 1,
            'function_calls' => $functionCalls,
            'method' => 'adaptive_search',
            'coarse_points' => $coarsePoints,
            'fine_points' => $finePoints
        ];
    }

    /**
     * Численная производная для проверки оптимальности
     */
    public static function derivative(float $x, float $h = 1e-6): float
    {
        return (self::f($x + $h) - self::f($x - $h)) / (2 * $h);
    }

    /**
     * Проверка, является ли точка минимумом
     */
    public static function isMinimumPoint(float $x, float $tolerance = 0.1): bool
    {
        $derivative = self::derivative($x);
        return abs($derivative) < $tolerance;
    }

    /**
     * Сравнение нескольких методов
     */
    public static function compareMethods(float $a, float $b, array $methods, int $points = 1000): array
    {
        $results = [];
        
        foreach ($methods as $method) {
            $startTime = microtime(true);
            $result = self::findLocalMinimum($a, $b, $method, 1e-6, $points);
            $endTime = microtime(true);
            
            if ($result['success']) {
                $result['execution_time'] = round($endTime - $startTime, 6);
                $results[$method] = $result;
            }
        }
        
        return $results;
    }
}
?>