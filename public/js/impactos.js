// Armazenar instâncias dos gráficos para poder destruí-los
let chartInstances = {};
let _chartCheckInterval = null;

// Função específica para a página de impactos
function initImpactos() {
    // Limpar intervalo anterior se existir
    if (_chartCheckInterval) {
        clearInterval(_chartCheckInterval);
        _chartCheckInterval = null;
    }
    
    // Verificar se Chart.js está carregado
    if (typeof Chart === 'undefined') {
        console.error('Chart.js não está carregado. Aguardando...');
        // Tentar novamente após um delay
        let tentativas = 0;
        _chartCheckInterval = setInterval(() => {
            tentativas++;
            if (typeof Chart !== 'undefined') {
                clearInterval(_chartCheckInterval);
                _chartCheckInterval = null;
                initCharts();
            } else if (tentativas >= 10) {
                console.error('Chart.js ainda não está disponível após 10 tentativas');
                clearInterval(_chartCheckInterval);
                _chartCheckInterval = null;
            }
        }, 200);
        
        // Registrar intervalo no sistema de recursos
        if (window._spaResources && _chartCheckInterval) {
            window._spaResources.intervals.push(_chartCheckInterval);
        }
        return;
    }
    
    initCharts();
}

// Função de limpeza para recursos da página de impactos
function cleanupImpactos() {
    // Limpar intervalo de verificação
    if (_chartCheckInterval) {
        clearInterval(_chartCheckInterval);
        _chartCheckInterval = null;
    }
    
    // Destruir todos os gráficos
    Object.keys(chartInstances).forEach(key => {
        try {
            if (chartInstances[key] && typeof chartInstances[key].destroy === 'function') {
                chartInstances[key].destroy();
            }
        } catch (error) {
            console.error(`Erro ao destruir gráfico ${key}:`, error);
        }
    });
    chartInstances = {};
}

// Registrar função de cleanup no sistema global
if (!window._cleanupFunctions) {
    window._cleanupFunctions = {};
}
window._cleanupFunctions.initImpactos = cleanupImpactos;

// Configuração de cores baseada no tema
function getChartColors() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    
    return {
        primary: isDark ? '#3b82f6' : '#2563eb',
        secondary: isDark ? '#60a5fa' : '#3b82f6',
        success: isDark ? '#10b981' : '#059669',
        warning: isDark ? '#f59e0b' : '#d97706',
        error: isDark ? '#ef4444' : '#dc2626',
        background: isDark ? '#1e293b' : '#f8fafc',
        text: isDark ? '#f8fafc' : '#1e293b',
        textSecondary: isDark ? '#cbd5e1' : '#475569'
    };
}

// Configuração padrão dos gráficos
function getChartConfig() {
    const colors = getChartColors();
    
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: colors.background,
                titleColor: colors.text,
                bodyColor: colors.textSecondary,
                borderColor: colors.primary,
                borderWidth: 1,
                cornerRadius: 8,
                displayColors: false
            }
        },
        scales: {
            x: {
                grid: {
                    color: colors.textSecondary,
                    opacity: 0.1
                },
                ticks: {
                    color: colors.textSecondary,
                    font: {
                        size: 12,
                        weight: '500'
                    }
                }
            },
            y: {
                grid: {
                    color: colors.textSecondary,
                    opacity: 0.1
                },
                ticks: {
                    color: colors.textSecondary,
                    font: {
                        size: 12,
                        weight: '500'
                    }
                }
            }
        },
        animation: {
            duration: 1500,
            easing: 'easeInOutQuart'
        }
    };
}

// Gráfico 1: Redução de Tempo (Gráfico de Pizza)
function createTimeReductionChart() {
    const canvas = document.getElementById('timeReductionChart');
    if (!canvas) {
        console.warn('Canvas timeReductionChart não encontrado');
        return;
    }
    
    // Destruir gráfico existente se houver
    if (chartInstances.timeReductionChart) {
        chartInstances.timeReductionChart.destroy();
        chartInstances.timeReductionChart = null;
    }
    
    const colors = getChartColors();
    
    try {
        chartInstances.timeReductionChart = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: ['Tempo Economizado', 'Tempo Restante'],
            datasets: [{
                data: [90, 30],
                backgroundColor: [colors.success, colors.textSecondary],
                borderColor: [colors.success, colors.textSecondary],
                borderWidth: 3,
                cutout: '60%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: colors.textSecondary,
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: colors.background,
                    titleColor: colors.text,
                    bodyColor: colors.textSecondary,
                    borderColor: colors.primary,
                    borderWidth: 1,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value}h (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });
    } catch (error) {
        console.error('Erro ao criar gráfico timeReductionChart:', error);
    }
}

// Gráfico 2: Economia Tributária (Gráfico de Área)
function createTaxSavingsChart() {
    const canvas = document.getElementById('taxSavingsChart');
    if (!canvas) {
        console.warn('Canvas taxSavingsChart não encontrado');
        return;
    }
    
    // Destruir gráfico existente se houver
    if (chartInstances.taxSavingsChart) {
        chartInstances.taxSavingsChart.destroy();
        chartInstances.taxSavingsChart = null;
    }
    
    const colors = getChartColors();
    
    try {
        chartInstances.taxSavingsChart = new Chart(canvas, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            datasets: [{
                label: 'Economia Mensal',
                data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 450],
                borderColor: colors.success,
                backgroundColor: `rgba(16, 185, 129, 0.1)`,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colors.success,
                pointBorderColor: colors.background,
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: colors.background,
                    titleColor: colors.text,
                    bodyColor: colors.textSecondary,
                    borderColor: colors.primary,
                    borderWidth: 1,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return `Economia: R$ ${context.parsed.y}K`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: colors.textSecondary,
                        opacity: 0.1
                    },
                    ticks: {
                        color: colors.textSecondary,
                        font: {
                            size: 11,
                            weight: '500'
                        }
                    }
                },
                y: {
                    grid: {
                        color: colors.textSecondary,
                        opacity: 0.1
                    },
                    ticks: {
                        color: colors.textSecondary,
                        font: {
                            size: 11,
                            weight: '500'
                        },
                        callback: function(value) {
                            return 'R$ ' + value + 'K';
                        }
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });
    } catch (error) {
        console.error('Erro ao criar gráfico taxSavingsChart:', error);
    }
}

// Gráfico 3: Redução de Erros (Gráfico de Barras Horizontais)
function createErrorReductionChart() {
    const canvas = document.getElementById('errorReductionChart');
    if (!canvas) {
        console.warn('Canvas errorReductionChart não encontrado');
        return;
    }
    
    // Destruir gráfico existente se houver
    if (chartInstances.errorReductionChart) {
        chartInstances.errorReductionChart.destroy();
        chartInstances.errorReductionChart = null;
    }
    
    const colors = getChartColors();
    
    try {
        chartInstances.errorReductionChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: ['Antes', 'Depois'],
            datasets: [{
                data: [45, 3],
                backgroundColor: [colors.warning, colors.success],
                borderColor: [colors.warning, colors.success],
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: colors.background,
                    titleColor: colors.text,
                    bodyColor: colors.textSecondary,
                    borderColor: colors.primary,
                    borderWidth: 1,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return `${context.parsed.x} erros por mês`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: colors.textSecondary,
                        opacity: 0.1
                    },
                    ticks: {
                        color: colors.textSecondary,
                        font: {
                            size: 11,
                            weight: '500'
                        },
                        callback: function(value) {
                            return value + ' erros';
                        }
                    }
                },
                y: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: colors.textSecondary,
                        font: {
                            size: 12,
                            weight: '600'
                        }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });
    } catch (error) {
        console.error('Erro ao criar gráfico errorReductionChart:', error);
    }
}

// Gráfico 4: ROI (Gráfico de Pizza com Centro)
function createROIChart() {
    const canvas = document.getElementById('roiChart');
    if (!canvas) {
        console.warn('Canvas roiChart não encontrado');
        return;
    }
    
    // Destruir gráfico existente se houver
    if (chartInstances.roiChart) {
        chartInstances.roiChart.destroy();
        chartInstances.roiChart = null;
    }
    
    const colors = getChartColors();
    
    try {
        chartInstances.roiChart = new Chart(canvas, {
        type: 'pie',
        data: {
            labels: ['Investimento', 'Lucro Líquido'],
            datasets: [{
                data: [50, 530],
                backgroundColor: [colors.primary, colors.success],
                borderColor: [colors.primary, colors.success],
                borderWidth: 3,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: colors.textSecondary,
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: colors.background,
                    titleColor: colors.text,
                    bodyColor: colors.textSecondary,
                    borderColor: colors.primary,
                    borderWidth: 1,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: R$ ${value}K (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });
    } catch (error) {
        console.error('Erro ao criar gráfico roiChart:', error);
    }
}

// Inicializar todos os gráficos
function initCharts() {
    // Verificar se Chart.js está disponível
    if (typeof Chart === 'undefined') {
        console.error('Chart.js não está disponível. Tentando novamente...');
        setTimeout(() => {
            if (typeof Chart !== 'undefined') {
                initCharts();
            } else {
                console.error('Chart.js ainda não está disponível após tentativa');
            }
        }, 500);
        return;
    }
    
    // Função para tentar criar os gráficos com retry
    function tryCreateCharts(tentativas = 0) {
        const canvasElements = [
            'timeReductionChart',
            'taxSavingsChart',
            'errorReductionChart',
            'roiChart',
            'efficiencyChart',
            'growthChart'
        ];
        
        const allExist = canvasElements.every(id => {
            const element = document.getElementById(id);
            return element !== null;
        });
        
        if (allExist) {
            createAllCharts();
        } else if (tentativas < 10) {
            // Tentar novamente após um delay
            setTimeout(() => {
                tryCreateCharts(tentativas + 1);
            }, 200);
        } else {
            console.error('Elementos canvas não foram encontrados após múltiplas tentativas');
        }
    }
    
    // Aguardar um pouco para garantir que os elementos estejam prontos
    setTimeout(() => {
        tryCreateCharts();
    }, 100);
}

// Gráfico 5: Eficiência Operacional (Barras Comparativas)
function createEfficiencyChart() {
    const canvas = document.getElementById('efficiencyChart');
    if (!canvas) {
        console.warn('Canvas efficiencyChart não encontrado');
        return;
    }
    
    // Destruir gráfico existente se houver
    if (chartInstances.efficiencyChart) {
        chartInstances.efficiencyChart.destroy();
        chartInstances.efficiencyChart = null;
    }
    
    const colors = getChartColors();
    
    try {
        chartInstances.efficiencyChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: ['Antes', 'Depois'],
            datasets: [{
                data: [35, 92],
                backgroundColor: [colors.error, colors.success],
                borderColor: [colors.error, colors.success],
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: colors.background,
                    titleColor: colors.text,
                    bodyColor: colors.textSecondary,
                    borderColor: colors.primary,
                    borderWidth: 1,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return `Eficiência: ${context.parsed.y}%`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: colors.textSecondary,
                        font: {
                            size: 12,
                            weight: '600'
                        }
                    }
                },
                y: {
                    grid: {
                        color: colors.textSecondary,
                        opacity: 0.1
                    },
                    ticks: {
                        color: colors.textSecondary,
                        font: {
                            size: 11,
                            weight: '500'
                        },
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });
    } catch (error) {
        console.error('Erro ao criar gráfico efficiencyChart:', error);
    }
}

// Gráfico 6: Produtividade da Equipe (Linha Simples)
function createGrowthChart() {
    const canvas = document.getElementById('growthChart');
    if (!canvas) {
        console.warn('Canvas growthChart não encontrado');
        return;
    }
    
    // Destruir gráfico existente se houver
    if (chartInstances.growthChart) {
        chartInstances.growthChart.destroy();
        chartInstances.growthChart = null;
    }
    
    const colors = getChartColors();
    
    try {
        chartInstances.growthChart = new Chart(canvas, {
        type: 'line',
        data: {
            labels: ['Q1', 'Q2', 'Q3', 'Q4'],
            datasets: [{
                label: 'Produtividade da Equipe',
                data: [65, 78, 85, 92],
                borderColor: colors.primary,
                backgroundColor: `rgba(59, 130, 246, 0.1)`,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colors.primary,
                pointBorderColor: colors.background,
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: colors.background,
                    titleColor: colors.text,
                    bodyColor: colors.textSecondary,
                    borderColor: colors.primary,
                    borderWidth: 1,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return `Produtividade: ${context.parsed.y}%`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: colors.textSecondary,
                        opacity: 0.1
                    },
                    ticks: {
                        color: colors.textSecondary,
                        font: {
                            size: 12,
                            weight: '600'
                        }
                    }
                },
                y: {
                    grid: {
                        color: colors.textSecondary,
                        opacity: 0.1
                    },
                    ticks: {
                        color: colors.textSecondary,
                        font: {
                            size: 11,
                            weight: '500'
                        },
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });
    } catch (error) {
        console.error('Erro ao criar gráfico growthChart:', error);
    }
}

function createAllCharts() {
    try {
        createTimeReductionChart();
        createTaxSavingsChart();
        createErrorReductionChart();
        createROIChart();
        createEfficiencyChart();
        createGrowthChart();
    } catch (error) {
        console.error('Erro ao criar gráficos:', error);
        console.error('Stack trace:', error.stack);
    }
}

// Recriar gráficos quando o tema mudar
document.addEventListener('themeChanged', function() {
    setTimeout(createAllCharts, 100);
});
