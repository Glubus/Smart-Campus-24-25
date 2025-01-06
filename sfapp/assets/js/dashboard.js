document.addEventListener("DOMContentLoaded", function () {
    // Fonction générique pour générer un graphique
    function createChart(canvasId, label, color, yLabel) {
        const chartCanvas = document.getElementById(canvasId);
        const dataFromServer = JSON.parse(chartCanvas.dataset.chartData); // Lire les données injectées par Twig.

        const labels = Object.keys(dataFromServer); // Les dates
        const values = labels.map(date => dataFromServer[date] || 0); // Les valeurs pour ce type

        const ctx = chartCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'line', // Type de graphique (ligne)
            data: {
                labels: labels, // Les dates
                datasets: [
                    {
                        label: label,
                        data: values,
                        borderColor: color,
                        backgroundColor: `${color.replace('rgb', 'rgba').replace(')', ', 0.2)')}`,
                        tension: 0.3,
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Dates'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: yLabel
                        }
                    }
                }
            }
        });
    }

    // Créez les trois graphiques
    createChart('co2Chart', 'CO2 (ppm)', 'rgb(255, 99, 132)', 'CO2 (ppm)');
    createChart('tempChart', 'Température (°C)', 'rgb(54, 162, 235)', 'Température (°C)');
    createChart('humChart', 'Humidité (%)', 'rgb(75, 192, 192)', 'Humidité (%)');
});