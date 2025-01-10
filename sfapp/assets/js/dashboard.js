window.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search); // Récupère les query params de l'URL
    const selectedValue = urlParams.get('period'); // Récupère la valeur de "period" (1, 7 ou 30)
    const timePeriodSelect = document.getElementById('timePeriod'); // Cible la liste déroulante

    // Vérifie si "period" correspond à une option du select
    if (selectedValue && timePeriodSelect.querySelector(`option[value="${selectedValue}"]`)) {
        timePeriodSelect.value = selectedValue;
    }

    // Redirection en réponse à un changement dans le menu déroulant
    timePeriodSelect.addEventListener('change', function () {
        const selectedValue = this.value; // Récupère la nouvelle valeur sélectionnée
        const url = new URL(window.location.href); // Crée un objet URL basé sur l'actuelle
        url.searchParams.set('period', selectedValue); // Ajoute ou remplace la query "period"
        window.location.href = url.toString(); // Recharge la page avec le nouvel URL
         });
});
document.addEventListener("DOMContentLoaded", function () {
    // Fonction générique pour générer un graphique
    function createChart(canvasId, label, color, yLabel, lowerLimit = null, upperLimit = null) {
        const chartCanvas = document.getElementById(canvasId);
        const dataFromServer = JSON.parse(chartCanvas.dataset.chartData); // Lire les données injectées par Twig.

        const labels = Object.keys(dataFromServer); // Les dates
        const values = labels.map(date => dataFromServer[date] || 0); // Les valeurs pour ce type

        // Vérifiez si une donnée dépasse la limite haute ou est en dessous de la limite basse
        const isBelowLowerLimit = lowerLimit !== null && values.some(value => value < lowerLimit);
        const isAboveUpperLimit = upperLimit !== null && values.some(value => value > upperLimit);

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
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    annotation: {
                        annotations: {
                            ...(isBelowLowerLimit ? {
                                lowerLimitLine: {
                                    type: 'line',
                                    yMin: lowerLimit,
                                    yMax: lowerLimit,
                                    borderColor: 'red',
                                    borderWidth: 2,
                                    label: {
                                        display: true,
                                        content: `Limite Basse (${lowerLimit})`,
                                        color: 'red',
                                        backgroundColor: 'rgba(0, 0, 0, 0.6)',
                                        position: 'start'
                                    }
                                }
                            } : {}),
                            ...(isAboveUpperLimit ? {
                                upperLimitLine: {
                                    type: 'line',
                                    yMin: upperLimit,
                                    yMax: upperLimit,
                                    borderColor: 'red',
                                    borderWidth: 2,
                                    label: {
                                        display: true,
                                        content: `Limite Haute (${upperLimit})`,
                                        color: 'red',
                                        backgroundColor: 'rgba(0, 0, 0, 0.6)',
                                        position: 'end'
                                    }
                                }
                            } : {})
                        }
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

    // Créez les trois graphiques avec des limites basse et haute
    createChart('co2Chart', 'CO2 (ppm)', 'rgb(255, 99, 132)', 'CO2 (ppm)', 250, 1200);
    createChart('tempChart', 'Température (°C)', 'rgb(255, 217, 0)', 'Température (°C)', 18, 22);
    createChart('humChart', 'Humidité (%)', 'rgb(106,223,255)', 'Humidité (%)', 40, 75);
});