const { computeY } = require("./functionResolver");
let chartInstance = null;

function drawChart(
  points,
  localMinX,
  localMinY,
  globalMinX,
  globalMinY,
  allLocalMinima,
  x0,
  searchRange
) {
  const chartCanvas = document.getElementById("chart");
  if (!chartCanvas) return;

  if (points.length === 0) return;

  // Определяем диапазон для графика
  const chartMinX = -searchRange;
  const chartMaxX = searchRange;

  // Создаем плавный график функции
  const graphPoints = [];
  const step = (chartMaxX - chartMinX) / 300;
  for (let x = chartMinX; x <= chartMaxX; x += step) {
    graphPoints.push({ x: x, y: computeY(x) });
  }

  if (chartInstance) {
    chartInstance.destroy();
  }

  const datasets = [
    {
      label: "Y(X) = X² + 0.5 - sin(3X)",
      data: graphPoints,
      borderColor: "blue",
      fill: false,
      pointRadius: 0,
      borderWidth: 2,
      tension: 0.4,
    },
  ];

  // Добавляем вычисленные точки
  if (points.length > 0) {
    datasets.push({
      label: "Вычисленные точки",
      data: points,
      borderColor: "red",
      backgroundColor: "red",
      pointRadius: 4,
      showLine: false,
    });
  }

  // Добавляем начальную точку
  datasets.push({
    label: "Начальная точка X₀",
    data: [{ x: x0, y: computeY(x0) }],
    borderColor: "orange",
    backgroundColor: "orange",
    pointRadius: 6,
    showLine: false,
  });

  // Добавляем все локальные минимумы
  if (allLocalMinima.length > 0) {
    datasets.push({
      label: "Все локальные минимумы",
      data: allLocalMinima,
      borderColor: "purple",
      backgroundColor: "purple",
      pointRadius: 4,
      showLine: false,
    });
  }

  // Добавляем найденный локальный минимум
  if (localMinX !== null) {
    datasets.push({
      label: "Найденный локальный минимум",
      data: [{ x: localMinX, y: localMinY }],
      borderColor: "green",
      backgroundColor: "green",
      pointRadius: 8,
      pointStyle: "circle",
      showLine: false,
    });
  }

  // Добавляем глобальный минимум
  datasets.push({
    label: "Глобальный минимум",
    data: [{ x: globalMinX, y: globalMinY }],
    borderColor: "gold",
    backgroundColor: "gold",
    pointRadius: 10,
    pointStyle: "star",
    showLine: false,
  });

  chartInstance = new Chart(chartCanvas, {
    type: "line",
    data: { datasets: datasets },
    options: {
      responsive: true,
      scales: {
        x: {
          type: "linear",
          position: "bottom",
          title: {
            display: true,
            text: "X",
          },
          min: chartMinX,
          max: chartMaxX,
        },
        y: {
          beginAtZero: false,
          title: {
            display: true,
            text: "Y(X)",
          },
        },
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: function (context) {
              return `X: ${context.parsed.x.toFixed(
                4
              )}, Y: ${context.parsed.y.toFixed(4)}`;
            },
          },
        },
      },
    },
  });
}

// Экспорт для тестов
if (typeof module !== "undefined" && module.exports) {
  module.exports = {
    drawChart,
  };
}
