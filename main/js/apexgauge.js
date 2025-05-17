/**
 * ApexCharts Gauge Chart for TechCidadão
 * Implementa um gráfico de velocímetro estilizado para visualização de qualidade
 */

class QualityGauge {
    constructor(elementId, options = {}) {
        this.elementId = elementId;
        this.options = Object.assign({
            min: 0,
            max: 100,
            value: 50,
            label: 'Qualidade',
            height: 350,
            animationDuration: 1000
        }, options);
        
        this.chart = null;
        this.currentValue = 0;
        this.targetValue = this.options.value;
    }
    
    // Obtém a cor baseada no valor
    getColor(value) {
        if (value < 25) return '#ff3860'; // Vermelho - Quente (Insatisfatório)
        if (value < 50) return '#ffdd57'; // Amarelo - Quente (Regular)
        if (value < 75) return '#48c774'; // Verde - Frio (Bom)
        return '#3273dc';                 // Azul - Muito frio (Excelente)
    }
    
    // Define a faixa de cores com base no valor atual
    getColorRange(value) {
        return [
            value >= 0 && value < 25 ? '#ff3860' : '#E0E0E0',    // Vermelho/Cinza
            value >= 25 && value < 50 ? '#ffdd57' : '#E0E0E0',  // Amarelo/Cinza
            value >= 50 && value < 75 ? '#48c774' : '#E0E0E0',  // Verde/Cinza
            value >= 75 && value <= 100 ? '#3273dc' : '#E0E0E0' // Azul/Cinza
        ];
    }
    
    // Inicializa o gráfico
    init() {
        const element = document.getElementById(this.elementId);
        if (!element) {
            console.error(`Elemento com ID ${this.elementId} não encontrado`);
            return;
        }
        
        // Definir o valor inicial
        const initialValue = this.options.value;
        
        // Obter as cores para o valor inicial
        const valueColors = this.getColorRange(initialValue);
        
        // Configurações do gráfico
        const options = {
            series: [initialValue], // Começa com o valor inicial para animação mais suave
            chart: {
                height: this.options.height,
                type: 'radialBar',
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: this.options.animationDuration,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },
            plotOptions: {
                radialBar: {
                    startAngle: -135,
                    endAngle: 135,
                    hollow: {
                        margin: 0,
                        size: '70%',
                        background: '#fff',
                        image: undefined,
                        imageOffsetX: 0,
                        imageOffsetY: 0,
                        position: 'front',
                        dropShadow: {
                            enabled: true,
                            top: 3,
                            left: 0,
                            blur: 4,
                            opacity: 0.24
                        }
                    },
                    track: {
                        background: '#f2f2f2',
                        strokeWidth: '100%',
                        margin: 0,
                        dropShadow: {
                            enabled: true,
                            top: -3,
                            left: 0,
                            blur: 4,
                            opacity: 0.15
                        }
                    },
                    dataLabels: {
                        show: true,
                        name: {
                            offsetY: -10,
                            show: true,
                            color: '#666',
                            fontSize: '17px'
                        },
                        value: {
                            offsetY: 5,
                            color: '#111',
                            fontSize: '36px',
                            show: true,
                            formatter: function (val) {
                                return val + '%';
                            }
                        }
                    }
                }
            },
            fill: {
                type: 'solid'
            },
            // Define a cor com base no valor
            colors: [this.getColor(initialValue)],
            stroke: {
                lineCap: 'round',
                width: 5
            },
            labels: [this.options.label]
        };
        
        // Criar o chart
        this.chart = new ApexCharts(element, options);
        this.chart.render();
        
        // Animar para o valor alvo
        setTimeout(() => {
            this.updateValue(this.options.value);
        }, 300);
    }
    
    // Atualiza o valor do gráfico
    updateValue(newValue) {
        this.targetValue = Math.max(this.options.min, Math.min(this.options.max, newValue));
        
        // Obter a cor correspondente ao novo valor
        const newColor = this.getColor(this.targetValue);
        
        // Atualizar o gráfico com a nova cor e valor
        this.chart.updateOptions({
            colors: [newColor]
        });
        this.chart.updateSeries([this.targetValue]);
    }
    
    // Atualiza a label do gráfico
    updateLabel(newLabel) {
        this.options.label = newLabel;
        this.chart.updateOptions({
            labels: [newLabel]
        });
    }
}

// Expõe a função para criar um gauge
window.createQualityGauge = function(elementId, options) {
    const gauge = new QualityGauge(elementId, options);
    gauge.init();
    return gauge;
};
