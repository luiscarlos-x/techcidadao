# Leiame - Instruções de Desenvolvimento

## Objetivos

- Desenvolver uma plataforma web colaborativa para avaliação de aspectos urbanos
- Promover o engajamento comunitário através de uma interface gamificada
- Fornecer representações visuais claras do sentimento coletivo sobre diferentes aspectos urbanos
- Criar um MVP (Produto Mínimo Viável) funcional como projeto piloto

## Requisitos Funcionais

### Sistema de Autenticação:
- Cadastro de usuários (e-mail e senha)
- Login de usuários
- Recuperação de senha

### Sistema de Consulta:
- Filtros por cidade e categoria
- Visualização de avaliações por categoria
- Apresentação de gráficos representativos das avaliações
- Sistema de comentários por categoria

### Sistema de Avaliação:
- Avaliação de categorias urbanas em escala tipo "temperatura"
- Registro das avaliações no banco de dados
- Atualização em tempo real dos gráficos

### Dashboard Informativo:
- Descrições das categorias
- Gráficos de temperatura/velocímetro
- Gráficos de barras comparativos
- Seção de comentários

## Requisitos Não-Funcionais

### Interface:
- Design moderno com tema azul e branco
- Layout responsivo para diferentes dispositivos
- Design limpo e intuitivo

### Performance:
- Tempo de carregamento das páginas inferior a 3 segundos
- Atualizações em tempo real dos dados

### Segurança:
- Proteção de dados dos usuários
- Validação de entradas para prevenir injeções SQL
- Criptografia de senhas

### Compatibilidade:
- Compatível com os principais navegadores (Chrome, Firefox, Safari, Edge)

## Arquitetura de Software

### Visão Geral
A aplicação seguirá o padrão arquitetural MVC (Model-View-Controller), implementado utilizando PHP para o backend, HTML e CSS para o frontend, e JavaScript para funcionalidades interativas.

### Componentes

#### Frontend:
- HTML5 para estruturação das páginas
- CSS3 para estilização (paleta azul e branca)
- JavaScript para interações e visualização dos gráficos

#### Backend:
- PHP para processamento de dados e lógica de negócios
- MySQL para armazenamento de dados

#### APIs/Bibliotecas:
- Biblioteca de gráficos para visualização dos dados (Chart.js)
- API de geolocalização para identificação de cidades

## Modelo de Dados

### Tabelas Principais:

#### Usuários:
- ID (PK)
- Nome
- Email
- Senha (hash)
- Data de registro

#### Cidades:
- ID (PK)
- Nome
- Estado
- Coordenadas

#### Categorias:
- ID (PK)
- Nome
- Descrição
- Imagem

#### Avaliações:
- ID (PK)
- ID_Usuário (FK)
- ID_Cidade (FK)
- ID_Categoria (FK)
- Pontuação
- Data

#### Comentários:
- ID (PK)
- ID_Usuário (FK)
- ID_Categoria (FK)
- ID_Cidade (FK)
- Conteúdo
- Data

## Protótipo de Interface

### Página Inicial
- Cabeçalho: Logo, menu de navegação, botões de login/cadastro
- Banner principal: Mensagem sobre o propósito da plataforma
- Seção informativa: Breve descrição do projeto e seus objetivos
- Seleção de cidade e categoria: Dropdown para filtrar avaliações

### Página de Categoria
- Imagem representativa: Visual relacionado à categoria selecionada
- Descrição: Explicação sobre a categoria e critérios de avaliação
- Gráfico de temperatura/velocímetro: Representação visual da média de avaliações
- Gráfico de barras: Detalhamento das avaliações por pontuação
- Seção de comentários: Feedback textual dos usuários

### Sistema de Login/Cadastro
- Formulário de cadastro: Campos para nome, email e senha
- Formulário de login: Email e senha
- Recuperação de senha: Opção para redefinição via email

### Rodapé
- Informações de contato
- Links para suporte
- Créditos dos desenvolvedores
- Política de privacidade e termos de uso

## Cronograma de Desenvolvimento

### Fase 1: Planejamento e Design (2 dias)
- Definição final dos requisitos
- Criação dos wireframes
- Design visual das interfaces
- Definição da arquitetura de dados

### Fase 2: Desenvolvimento Frontend (3 dias)
- Implementação do HTML e CSS base
- Desenvolvimento das páginas estáticas
- Integração dos componentes visuais
- Implementação dos gráficos

### Fase 3: Desenvolvimento Backend (3 dias)
- Configuração do ambiente de servidor
- Implementação do banco de dados
- Desenvolvimento das APIs e controladores
- Sistema de autenticação

### Fase 4: Integração e Testes (2 dias)
- Integração frontend-backend
- Testes de funcionalidade
- Testes de usabilidade
- Correções e ajustes
