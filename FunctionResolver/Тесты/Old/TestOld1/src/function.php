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
     * Находит локальный минимум функции методом золотого сечения
     */
    public static function findLocalMinimum(float $a = -2, float $b = 2, float $tol = 1e-6): array
    {
        // Проверка корректности границ
        if ($a >= $b) {
            return [
                'success' => false,
                'error' => 'Invalid bounds: a must be less than b'
            ];
        }

        $goldenRatio = (1 + sqrt(5)) / 2;
        
        $c = $b - ($b - $a) / $goldenRatio;
        $d = $a + ($b - $a) / $goldenRatio;
        
        $iterations = 0;
        $maxIterations = 100;
        
        while (abs($c - $d) > $tol && $iterations < $maxIterations) {
            if (self::f($c) < self::f($d)) {
                $b = $d;
            } else {
                $a = $c;
            }
            
            $c = $b - ($b - $a) / $goldenRatio;
            $d = $a + ($b - $a) / $goldenRatio;
            $iterations++;
        }
        
        $x_min = ($b + $a) / 2;
        
        return [
            'success' => true,
            'x' => $x_min,
            'fun' => self::f($x_min),
            'iterations' => $iterations,
            'method' => 'golden_section'
        ];
    }

    /**
     * Поиск минимума методом перебора
     */
    public static function bruteForceSearch(float $a = -2, float $b = 2, int $numPoints = 1000): array
    {
        if ($a >= $b) {
            return [
                'success' => false,
                'error' => 'Invalid bounds: a must be less than b'
            ];
        }

        $step = ($b - $a) / $numPoints;
        $x_min = $a;
        $f_min = self::f($a);
        
        for ($i = 1; $i <= $numPoints; $i++) {
            $x = $a + $i * $step;
            $f_x = self::f($x);
            
            if ($f_x < $f_min) {
                $f_min = $f_x;
                $x_min = $x;
            }
        }
        
        return [
            'success' => true,
            'x' => $x_min,
            'fun' => $f_min,
            'method' => 'brute_force',
            'points_evaluated' => $numPoints
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
     * Проверка, является ли точка минимумом (производная близка к 0)
     */
    public static function isMinimumPoint(float $x, float $tolerance = 0.1): bool
    {
        $derivative = self::derivative($x);
        return abs($derivative) < $tolerance;
    }
}
?>