# Plano de Implementação do Sistema de Avaliação – TechCidadão

## 1. Estruturação da Seção de Avaliação

**1.1.** Garantir que a seção "Cidade de Matões" contenha:
- Dropdown para seleção de bairro.
- Botões para seleção de categoria.

**1.2.** Preparar o HTML para receber os gráficos e o campo de comentários logo abaixo dos critérios.

---

## 2. Implementação dos Gráficos

**2.1.** Escolher e integrar uma biblioteca de gráficos (ex: [Chart.js](https://www.chartjs.org/) ou [ApexCharts](https://apexcharts.com/)).

**2.2.** Gráfico 1: "Temperatura" (velocímetro)
- Exibir um velocímetro estilizado do azul (ruim) ao vermelho (ótimo).
- Animação suave ao carregar e ao atualizar.
- Atualização em tempo real (via AJAX ou WebSocket).

**2.3.** Gráfico 2: Barras
- Exibir barras para cada critério da categoria (os critérios podem ser definidos diretamente no frontend/back-end, sem tabela dedicada).
- Animação de carregamento e atualização em tempo real.

---

## 3. Campo de Comentários

**3.1.** Criar área de comentários abaixo dos gráficos.
- Exibir comentários existentes para todos.
- Campo de texto estilizado para novos comentários (apenas usuários logados).

**3.2.** Botão de envio visível apenas para usuários cadastrados/logados.

---

## 4. Regras de Permissão

**4.1.** Usuário não registrado:
- Pode consultar bairros/categorias, ver gráficos e ler comentários.
- Não pode avaliar nem comentar.

**4.2.** Usuário registrado:
- Pode consultar, avaliar (uma vez por bairro/categoria) e comentar.

---

## 5. Backend e Banco de Dados

**5.1.** Criar tabelas para avaliações e comentários:
- Tabela `avaliacoes`: id, usuario_id, bairro, categoria, notas, data.
- Tabela `comentarios`: id, usuario_id, bairro, categoria, comentario, data.

**5.2.** Endpoints PHP para:
- Buscar avaliações e comentários (GET).
- Enviar avaliação (POST, apenas logado, uma vez por bairro/categoria).
- Enviar comentário (POST, apenas logado).

---

## 6. Integração Frontend/Backend

**6.1.** Ao selecionar bairro/categoria:
- Buscar dados via AJAX e atualizar gráficos/comentários em tempo real.

**6.2.** Ao enviar avaliação/comentário:
- Validar login.
- Atualizar gráficos e comentários sem recarregar a página.

---

## 7. Estilização

**7.1.** Garantir que todos os elementos (gráficos, botões, campos) sigam a paleta azul do TechCidadão.

**7.2.** Usar animações suaves e responsividade para mobile, tablet e desktop.

---

## 8. Testes

**8.1.** Testar todos os fluxos:
- Usuário não logado: consulta, gráficos, leitura de comentários.
- Usuário logado: consulta, avaliação única, comentários.

**8.2.** Testar responsividade e animações.

---

## 9. Documentação e Ajustes Finais

**9.1.** Documentar endpoints, estrutura de dados e regras de permissão.

**9.2.** Ajustar detalhes visuais e de usabilidade conforme feedback.

---

**Observação:**  
Implemente cada etapa separadamente, testando e validando antes de avançar para a próxima.

---