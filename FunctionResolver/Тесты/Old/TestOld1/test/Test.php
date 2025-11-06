<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

require 'function.php';

final class TestFunctionOptimizer extends TestCase
{
    private const TOLERANCE = 1e-3;

    protected function setUp(): void
    {
        // Очистка не требуется, так как класс статический
    }

    /**
     * Тест 1: Корректность вычисления функции
     */
    public function testFunctionCalculation(): void
    {
        $testCases = [
            [
                'input' => 0.0,
                'expected' => 0.5, // f(0) = 0² + 0.5 - sin(0) = 0.5
            ],
            [
                'input' => 1.0,
                'expected' => 1.0 + 0.5 - sin(3), // f(1) = 1 + 0.5 - sin(3)
            ],
            [
                'input' => -1.0,
                'expected' => 1.0 + 0.5 - sin(-3), // f(-1) = 1 + 0.5 - sin(-3)
            ],
        ];

        foreach ($testCases as $testCase) {
            $input = $testCase['input'];
            $expected = $testCase['expected'];
            
            $result = FunctionOptimizer::f($input);
            $this->assertEqualsWithDelta($expected, $result, self::TOLERANCE,
                "f({$input}) вычислена некорректно");
        }
    }

    /**
     * Тест 2: Поиск локального минимума методом золотого сечения
     */
    public function testGoldenSectionMinimum(): void
    {
        $result = FunctionOptimizer::findLocalMinimum(-2, 2);
        
        $this->assertTrue($result['success'], "Поиск минимума не удался");
        $this->assertArrayHasKey('x', $result, "Не найдена точка минимума");
        $this->assertArrayHasKey('fun', $result, "Не найдено значение минимума");
        
        $x_min = $result['x'];
        $f_min = $result['fun'];
        
        // Проверяем, что точка является минимумом (производная близка к 0)
        $isMinimum = FunctionOptimizer::isMinimumPoint($x_min);
        $this->assertTrue($isMinimum, "Найденная точка не является минимумом");
        
        // Проверяем значения в соседних точках
        $neighbors = [$x_min - 0.1, $x_min + 0.1];
        foreach ($neighbors as $neighbor) {
            $f_neighbor = FunctionOptimizer::f($neighbor);
            $this->assertGreaterThanOrEqual($f_min - self::TOLERANCE, $f_neighbor,
                "Найденная точка {$x_min} не является минимумом");
        }
    }

    /**
     * Тест 3: Сравнение метода золотого сечения и перебора
     */
    public function testMethodComparison(): void
    {
        $goldenResult = FunctionOptimizer::findLocalMinimum(-2, 2);
        $bruteResult = FunctionOptimizer::bruteForceSearch(-2, 2);
        
        $this->assertTrue($goldenResult['success'], "Метод золотого сечения не удался");
        $this->assertTrue($bruteResult['success'], "Метод перебора не удался");
        
        // Сравниваем результаты двух методов
        $diff_x = abs($goldenResult['x'] - $bruteResult['x']);
        $diff_fun = abs($goldenResult['fun'] - $bruteResult['fun']);
        
        $this->assertLessThan(0.1, $diff_x, "Разница в точках минимума слишком велика");
        $this->assertLessThan(0.01, $diff_fun, "Разница в значениях функции слишком велика");
    }

    /**
     * Тест 4: Обработка неверных границ
     */
    public function testInvalidBounds(): void
    {
        // Границы в неправильном порядке
        $result = FunctionOptimizer::findLocalMinimum(2, -2);
        $this->assertFalse($result['success'], "Должна быть ошибка при неверных границах");
        $this->assertStringContainsString('Invalid bounds', $result['error']);

        // Метод перебора с неверными границами
        $result = FunctionOptimizer::bruteForceSearch(2, -2);
        $this->assertFalse($result['success'], "Должна быть ошибка при неверных границах");
        $this->assertStringContainsString('Invalid bounds', $result['error']);
    }

    /**
     * Тест 5: Проверка свойств минимума
     */
    public function testMinimumProperties(): void
    {
        $result = FunctionOptimizer::findLocalMinimum(-2, 2);
        
        $this->assertTrue($result['success'], "Оптимизация не удалась");
        
        $x_min = $result['x'];
        $f_min = $result['fun'];
        
        // Значение функции в минимуме должно быть положительным
        $this->assertGreaterThanOrEqual(0, $f_min, "Значение минимума должно быть ≥ 0");
        
        // Точка минимума должна быть в заданных пределах
        $this->assertGreaterThanOrEqual(-2, $x_min, "Точка минимума слишком мала");
        $this->assertLessThanOrEqual(2, $x_min, "Точка минимума слишком велика");
        
        // Количество итераций должно быть разумным
        $this->assertLessThan(100, $result['iterations'], 
            "Слишком много итераций: {$result['iterations']}");
    }

    /**
     * DataProvider для тестирования на разных интервалах
     */
    public static function intervalDataProvider(): array
    {
        return [
            'standard interval' => [[-2, 2], true],
            'small interval' => [[-1, 1], true],
            'positive interval' => [[0, 2], true],
            'negative interval' => [[-2, 0], true],
        ];
    }

    #[DataProvider('intervalDataProvider')]
    public function testDifferentIntervals(array $bounds, bool $expectedSuccess): void
    {
        $result = FunctionOptimizer::findLocalMinimum($bounds[0], $bounds[1]);
        
        $this->assertEquals($expectedSuccess, $result['success']);
        
        if ($expectedSuccess) {
            $this->assertArrayHasKey('x', $result);
            $this->assertArrayHasKey('fun', $result);
            
            // Проверяем, что точка в пределах интервала
            $this->assertGreaterThanOrEqual($bounds[0], $result['x']);
            $this->assertLessThanOrEqual($bounds[1], $result['x']);
        }
    }

    /**
     * Тест производительности и точности
     */
    public function testPerformanceAndAccuracy(): void
    {
        // Тестируем метод золотого сечения
        $startTime = microtime(true);
        $result = FunctionOptimizer::findLocalMinimum(-2, 2, 1e-8); // высокая точность
        $endTime = microtime(true);
        
        $this->assertTrue($result['success']);
        
        // Время выполнения должно быть разумным (менее 1 секунды)
        $executionTime = $endTime - $startTime;
        $this->assertLessThan(1.0, $executionTime, 
            "Время выполнения слишком долгое: {$executionTime} сек");
        
        // Проверяем точность (производная должна быть близка к 0)
        $derivative = FunctionOptimizer::derivative($result['x']);
        $this->assertLessThan(0.01, abs($derivative), 
            "Точность недостаточна, производная: {$derivative}");
    }
}
?>