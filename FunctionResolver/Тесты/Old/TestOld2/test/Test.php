<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

require 'function.php';

final class TestFunctionOptimizer extends TestCase
{
    private const TOLERANCE = 1e-3;

    protected function setUp(): void{}

    /**
     * DataProvider для тестирования разных методов и параметров
     */
    public static function optimizationDataProvider(): array
    {
        return [
            // Стандартные тесты для всех методов
            'golden_section_standard' => [
                ['method' => 'golden_section', 'a' => -2, 'b' => 2],
                true
            ],
            'uniform_search_standard' => [
                ['method' => 'uniform_search', 'a' => -2, 'b' => 2, 'numPoints' => 500],
                true
            ],
            'random_search_standard' => [
                ['method' => 'random_search', 'a' => -2, 'b' => 2, 'numPoints' => 500],
                true
            ],
            'adaptive_search_standard' => [
                ['method' => 'adaptive_search', 'a' => -2, 'b' => 2, 'numPoints' => 500],
                true
            ],
            
            // Разные интервалы
            'small_interval' => [
                ['method' => 'golden_section', 'a' => 0, 'b' => 1],
                true
            ],
            'negative_interval' => [
                ['method' => 'uniform_search', 'a' => -3, 'b' => -1],
                true
            ],
            'large_interval' => [
                ['method' => 'adaptive_search', 'a' => -5, 'b' => 5],
                true
            ],
            
            // Разные точности
            'high_precision' => [
                ['method' => 'golden_section', 'a' => -2, 'b' => 2, 'tolerance' => 1e-8],
                true
            ],
            'low_precision' => [
                ['method' => 'golden_section', 'a' => -2, 'b' => 2, 'tolerance' => 1e-3],
                true
            ],
            
            // Разное количество точек
            'few_points' => [
                ['method' => 'uniform_search', 'a' => -2, 'b' => 2, 'numPoints' => 10],
                true
            ],
            'many_points' => [
                ['method' => 'uniform_search', 'a' => -2, 'b' => 2, 'numPoints' => 10000],
                true
            ],
        ];
    }

    /**
     * DataProvider для ошибочных сценариев
     */
    public static function errorDataProvider(): array
    {
        return [
            'invalid_bounds' => [
                ['method' => 'golden_section', 'a' => 2, 'b' => -2],
                'Недопустимые границы интервала'
            ],
            'invalid_method' => [
                ['method' => 'invalid_method', 'a' => -2, 'b' => 2],
                'Неизвестный метод оптимизации'
            ],
            'negative_tolerance' => [
                ['method' => 'golden_section', 'a' => -2, 'b' => 2, 'tolerance' => -1e-6],
                'Недопустимая точность'
            ],
            'zero_points' => [
                ['method' => 'uniform_search', 'a' => -2, 'b' => 2, 'numPoints' => 0],
                'Недопустимое количество точек'
            ],
            'negative_points' => [
                ['method' => 'uniform_search', 'a' => -2, 'b' => 2, 'numPoints' => -100],
                'Недопустимое количество точек'
            ],
        ];
    }

    #[DataProvider('optimizationDataProvider')]
    public function testOptimizationMethods(array $input, bool $expectedSuccess): void
    {
        $result = FunctionOptimizer::findLocalMinimum(
            $input['a'] ?? -2,
            $input['b'] ?? 2,
            $input['method'] ?? 'golden_section',
            $input['tolerance'] ?? 1e-6,
            $input['numPoints'] ?? 1000
        );
        
        $this->assertEquals($expectedSuccess, $result['success'],
            "Метод {$input['method']} на интервале [{$input['a']}, {$input['b']}]");
        
        if ($expectedSuccess) {
            $this->assertArrayHasKey('x', $result);
            $this->assertArrayHasKey('fun', $result);
            $this->assertIsFloat($result['x']);
            $this->assertIsFloat($result['fun']);
            
            // Проверяем, что точка в пределах интервала
            $this->assertGreaterThanOrEqual($input['a'], $result['x']);
            $this->assertLessThanOrEqual($input['b'], $result['x']);
            
            // Проверяем, что значение функции положительное
            $this->assertGreaterThanOrEqual(0, $result['fun']);
        }
    }

    #[DataProvider('errorDataProvider')]
    public function testErrorHandling(array $input, string $expectedError): void
    {
        $result = FunctionOptimizer::findLocalMinimum(
            $input['a'] ?? -2,
            $input['b'] ?? 2,
            $input['method'] ?? 'golden_section',
            $input['tolerance'] ?? 1e-6,
            $input['numPoints'] ?? 1000
        );
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString($expectedError, $result['error']);
    }

    /**
     * Тест сравнения всех методов
     */
    public function testMethodComparison(): void
    {
        $methods = ['golden_section', 'uniform_search', 'random_search', 'adaptive_search'];
        $results = FunctionOptimizer::compareMethods(-2, 2, $methods, 1000);
        
        $this->assertCount(count($methods), $results,
            "Должны быть результаты для всех методов");
        
        // Проверяем, что все методы дали схожие результаты
        $xValues = [];
        $fValues = [];
        
        foreach ($results as $method => $result) {
            $xValues[$method] = $result['x'];
            $fValues[$method] = $result['fun'];
            
            // Проверяем структуру результата
            $this->assertArrayHasKey('x', $result);
            $this->assertArrayHasKey('fun', $result);
            $this->assertArrayHasKey('execution_time', $result);
        }
        
        // Проверяем согласованность результатов
        $xRange = max($xValues) - min($xValues);
        $fRange = max($fValues) - min($fValues);
        
        $this->assertLessThan(0.1, $xRange, "Разброс точек минимума слишком велик");
        $this->assertLessThan(0.01, $fRange, "Разброс значений функции слишком велик");
    }

    /**
     * Тест производительности методов
     */
    public function testPerformance(): void
    {
        $methods = ['golden_section', 'uniform_search', 'random_search', 'adaptive_search'];
        
        foreach ($methods as $method) {
            $startTime = microtime(true);
            $result = FunctionOptimizer::findLocalMinimum(-2, 2, $method, 1e-6, 1000);
            $endTime = microtime(true);
            
            $this->assertTrue($result['success'],
                "Метод {$method} должен завершиться успешно");
            
            $executionTime = $endTime - $startTime;
            $this->assertLessThan(5.0, $executionTime,
                "Метод {$method} слишком медленный: {$executionTime} сек");
        }
    }

    /**
     * Тест точности методов
     */
    public function testAccuracy(): void
    {
        $testCases = [
            ['method' => 'golden_section', 'tolerance' => 1e-8],
            ['method' => 'uniform_search', 'numPoints' => 10000],
            ['method' => 'adaptive_search', 'numPoints' => 5000],
        ];
        
        foreach ($testCases as $testCase) {
            $result = FunctionOptimizer::findLocalMinimum(-2, 2, 
                $testCase['method'], 
                $testCase['tolerance'] ?? 1e-6,
                $testCase['numPoints'] ?? 1000
            );
            
            $this->assertTrue($result['success']);
            
            // Проверяем производную в найденной точке
            $derivative = FunctionOptimizer::derivative($result['x']);
            $this->assertLessThan(0.01, abs($derivative),
                "Метод {$testCase['method']} недостаточно точен. Производная: {$derivative}");
        }
    }

    /**
     * Тест на разных функциях (расширяемость)
     */
    public function testFunctionCalculation(): void
    {
        $testPoints = [-2, -1, 0, 0.5, 1, 2];
        $expectedValues = [];
        
        // Рассчитываем ожидаемые значения
        foreach ($testPoints as $x) {
            $expectedValues[$x] = $x*$x + 0.5 - sin(3*$x);
        }
        
        // Проверяем вычисления функции
        foreach ($testPoints as $x) {
            $actual = FunctionOptimizer::f($x);
            $expected = $expectedValues[$x];
            
            $this->assertEqualsWithDelta($expected, $actual, self::TOLERANCE,
                "Неверное значение функции в точке x = {$x}");
        }
    }

    /**
     * Тест граничных случаев
     */
    public function testBoundaryCases(): void
    {
        $boundaryCases = [
            ['a' => -1.0, 'b' => -1.0 + 1e-10], // Очень маленький интервал
            ['a' => -10, 'b' => 10],             // Очень большой интервал
            ['a' => 0.5, 'b' => 0.6],            // Интервал рядом с минимумом
            ['a' => -0.1, 'b' => 0.1],           // Интервал вокруг 0
        ];
        
        foreach ($boundaryCases as $case) {
            $result = FunctionOptimizer::findLocalMinimum($case['a'], $case['b'], 'golden_section');
            $this->assertTrue($result['success'],
                "Должен работать на интервале [{$case['a']}, {$case['b']}]");
        }
    }
}
?>