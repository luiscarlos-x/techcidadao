// Declarar variáveis e funções no escopo global para acesso externo
let selectedNeighborhoodName = 'Bairro1'; // Definir um valor padrão
let categoryData = {};
let categoryButtons;
let categoryDetails;

// Funções que serão acessíveis globalmente
async function loadNeighborhoodData(bairro) {
    try {
        // Adicionar um parâmetro de timestamp para evitar cache
        const timestamp = new Date().getTime();
        const response = await fetch(`api/get_avaliacoes.php?bairro=${encodeURIComponent(bairro)}&t=${timestamp}`);
        const jsonData = await response.json();
        
        if (jsonData.error) {
            console.error('Erro ao carregar dados do bairro:', jsonData.error);
            return false;
        }
        
        if (jsonData.data && jsonData.data.length > 0) {
            // Atualizar os dados das categorias com as médias reais do banco de dados
            jsonData.data.forEach(item => {
                const slug = item.categoria_slug;
                if (categoryData[slug]) {
                    categoryData[slug].qualityScore = parseInt(item.media) || 0;
                    categoryData[slug].totalAvaliacoes = parseInt(item.total_avaliacoes) || 0;
                }
            });
            return true;
        }
        
        return false;
    } catch (error) {
        console.error('Erro ao carregar dados do bairro:', error);
        return false;
    }
}

// Função para exibir detalhes da categoria
function showCategoryDetails(category) {
    console.log('Exibindo detalhes da categoria:', category);
    const data = categoryData[category];
    
    if (!data) {
        console.log('Dados da categoria não encontrados');
        return;
    }
    
    let criteriaHTML = '';
    data.criteria.forEach(criterion => {
        criteriaHTML += `<li>${criterion}</li>`;
    });
    
    // Verificar qual imagem usar
    let imagePath;
    if (data.image) {
        imagePath = 'images/' + data.image;
    } else {
        imagePath = 'images/placeholder.png';
    }
    
    const html = `
        <div class="category-content">
            <h4>${data.title}</h4>
            <div class="category-info-container">
                <div class="category-image">
                    <img src="${imagePath}" alt="${data.title}">
                </div>
                <div class="category-info">
                    <p class="category-description">${data.description}</p>
                    <div class="category-criteria">
                        <h5>Critérios de Avaliação:</h5>
                        <ul>${criteriaHTML}</ul>
                    </div>
                </div>
            </div>
            <div class="gauge-container">
                <div class="gauge-title">Índice de Qualidade</div>
                <div id="quality-gauge" class="gauge-chart"></div>
                <div class="gauge-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #ff3860;"></div>
                        <span class="legend-label">Insatisfatório</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #ffdd57;"></div>
                        <span class="legend-label">Regular</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #48c774;"></div>
                        <span class="legend-label">Bom</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #3273dc;"></div>
                        <span class="legend-label">Excelente</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    categoryDetails.innerHTML = html;
    categoryDetails.style.display = 'block';
    
    // Destacar botão selecionado
    categoryButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.category === category) {
            btn.classList.add('active');
        }
    });
    
    // Inicializar o gráfico de gauge após renderizar o conteúdo
    setTimeout(() => {
        if (data.qualityScore !== undefined) {
            const qualityScore = data.qualityScore;
            // Criar o gauge chart com ApexCharts
            window.qualityGauge = createQualityGauge('quality-gauge', {
                value: qualityScore,
                min: 0,
                max: 100,
                height: 320,
                label: 'Qualidade',
                animationDuration: 1000
            });
        }
    }, 100); // Pequeno atraso para garantir que o DOM esteja pronto
}

// Função para inicializar a página com dados padrão
async function initializePageWithDefaultData() {
    // Carregar dados do bairro padrão (Bairro1)
    const bairroDefault = 'Bairro1';
    const dataLoaded = await loadNeighborhoodData(bairroDefault);
    
    // Se os dados forem carregados com sucesso, exibir a primeira categoria
    if (dataLoaded && categoryButtons && categoryButtons.length > 0) {
        // Usar a primeira categoria disponível como padrão
        const defaultCategory = categoryButtons[0].dataset.category;
        showCategoryDetails(defaultCategory);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Neighborhood dropdown functionality
    const selectedNeighborhood = document.getElementById('selectedNeighborhood');
    const neighborhoodOptions = document.getElementById('neighborhoodOptions');
    const neighborhoodOptionsList = document.querySelectorAll('.neighborhood-option');
    
    // Toggle dropdown when clicking on the selected neighborhood
    if (selectedNeighborhood) {
        selectedNeighborhood.addEventListener('click', function() {
            neighborhoodOptions.classList.toggle('show');
        });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.neighborhood-dropdown')) {
            neighborhoodOptions.classList.remove('show');
        }
    });
    
    // Handle neighborhood selection
    neighborhoodOptionsList.forEach(option => {
        option.addEventListener('click', async function() {
            const neighborhood = this.textContent;
            selectedNeighborhood.textContent = neighborhood;
            neighborhoodOptions.classList.remove('show');
            
            // Armazenar o nome do bairro selecionado globalmente
            selectedNeighborhoodName = neighborhood;
            
            // Carregar dados do bairro selecionado
            const dataLoaded = await loadNeighborhoodData(neighborhood);
            
            // Limpar os detalhes da categoria exibidos atualmente
            categoryDetails.innerHTML = '';
            categoryDetails.style.display = 'none';
            
            // Remover classe ativa de todos os botões
            categoryButtons.forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Selecionar automaticamente a primeira categoria após carregar os dados do bairro
            if (dataLoaded && categoryButtons && categoryButtons.length > 0) {
                const firstCategory = categoryButtons[0].dataset.category;
                showCategoryDetails(firstCategory);
            }
        });
    });
    
    console.log('Script carregado');
    categoryButtons = document.querySelectorAll('.category-btn');
    console.log('Botões encontrados:', categoryButtons.length);
    categoryDetails = document.getElementById('category-details');
    console.log('Elemento category-details:', categoryDetails);
    
    // Dados estáticos das categorias (serão complementados com dados do banco de dados)
    categoryData = {
        iluminacao: {
            title: 'Iluminação Pública',
            image: 'iluminacao.png',
            qualityScore: 0, // Será atualizado com dados reais do banco
            description: 'A iluminação pública é essencial para a segurança e bem-estar dos cidadãos, permitindo a utilização dos espaços públicos durante a noite e contribuindo para a redução da criminalidade.',
            criteria: [
                'Cobertura: Percentual de vias públicas com iluminação adequada',
                'Qualidade: Estado de conservação das luminárias',
                'Eficiência: Uso de tecnologias de baixo consumo (LED)',
                'Manutenção: Tempo médio de resposta para reparos'
            ]
        },
        residuos: {
            title: 'Coleta de Resíduos',
            image: 'residuos.png',
            qualityScore: 0, // Será atualizado com dados reais do banco
            description: 'A gestão eficiente de resíduos é fundamental para a saúde pública e preservação do meio ambiente, envolvendo coleta, transporte, tratamento e disposição final adequada.',
            criteria: [
                'Regularidade: Frequência e pontualidade da coleta',
                'Abrangência: Cobertura do serviço nos bairros',
                'Coleta seletiva: Disponibilidade de coleta separada para reciclagem',
                'Limpeza urbana: Varrição de vias públicas e limpeza de áreas comuns'
            ]
        },
        transporte: {
            title: 'Transporte Público',
            image: 'transporte.png',
            qualityScore: 0, // Será atualizado com dados reais do banco
            description: 'O transporte público de qualidade é um direito essencial que contribui para a mobilidade urbana, redução de congestionamentos e poluição, e inclusão social dos cidadãos.',
            criteria: [
                'Disponibilidade: Frequência e horários do serviço',
                'Pontualidade: Cumprimento dos horários programados',
                'Conforto: Qualidade dos veículos e lotação',
                'Acessibilidade: Adaptação para pessoas com mobilidade reduzida'
            ]
        },
        seguranca: {
            title: 'Segurança Pública',
            image: 'seguranca.png',
            qualityScore: 0, // Será atualizado com dados reais do banco
            description: 'A segurança pública é direito fundamental para o bem-estar e qualidade de vida dos cidadãos, envolvendo prevenção, vigilância e combate à criminalidade.',
            criteria: [
                'Policiamento: Presença e efetividade das forças policiais',
                'Iluminação: Relação com segurança em áreas públicas',
                'Criminalidade: Índices e tendências nos bairros',
                'Sensação de segurança: Percepção dos moradores'
            ]
        },
        saude: {
            title: 'Saúde',
            image: 'saude.png',
            qualityScore: 0, // Será atualizado com dados reais do banco
            description: 'O acesso a serviços de saúde de qualidade é fundamental para o bem-estar da população, incluindo atendimento básico, especializado e de emergência.',
            criteria: [
                'Disponibilidade: Postos de saúde e hospitais próximos',
                'Atendimento: Tempo de espera e qualidade do serviço',
                'Medicamentos: Disponibilidade na rede pública',
                'Programas preventivos: Vacinação e campanhas de saúde'
            ]
        },
        infraestrutura: {
            title: 'Infraestrutura Urbana',
            image: 'infraestrutura.png',
            qualityScore: 0, // Será atualizado com dados reais do banco
            description: 'A infraestrutura urbana é a base para funcionamento da cidade, incluindo vias, calçadas, saneamento básico, drenagem e outros elementos essenciais para a vida urbana.',
            criteria: [
                'Pavimentação: Estado de conservação das vias',
                'Calçadas: Acessibilidade e manutenção',
                'Saneamento: Rede de esgoto e tratamento',
                'Drenagem: Sistema para prevenção de alagamentos'
            ]
        },
        lazer: {
            title: 'Lazer e Áreas Verdes',
            image: 'lazer.png',
            qualityScore: 0, // Será atualizado com dados reais do banco
            description: 'Espaços de lazer e áreas verdes são essenciais para qualidade de vida, proporcionando opções de recreação, prática esportiva, convívio social e contato com a natureza.',
            criteria: [
                'Praças: Disponibilidade e conservação',
                'Parques: Áreas verdes para uso público',
                'Equipamentos: Estruturas para atividades esportivas',
                'Programação: Eventos culturais e de entretenimento'
            ]
        }
    };
    
    // Adicionar evento de clique aos botões
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('Botão clicado:', this.dataset.category);
            const category = this.dataset.category;
            showCategoryDetails(category);
        });
    });
    
    // Inicializar a página com dados do bairro padrão e primeira categoria
    initializePageWithDefaultData();
});
