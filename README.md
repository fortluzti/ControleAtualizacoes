# ControleAtualizacoes

Sistema web para controle de solicitações de alteração, correção e melhoria de um sistema ERP.

---

## 1. Descrição

O **ControleAtualizacoes** é um sistema web desenvolvido para gerenciar todo o ciclo de vida das solicitações de alteração, correção e melhoria do sistema ERP utilizado pela empresa.

O sistema permite registrar, acompanhar, testar e avaliar todas as solicitações feitas à equipe externa de suporte e desenvolvimento, mantendo um histórico completo de todas as movimentações realizadas.

---

## 2. Objetivo

Centralizar e controlar todas as solicitações de alteração do ERP, desde a abertura até a conclusão final, garantindo:

- **Rastreabilidade** completa de cada solicitação
- **Histórico** detalhado de todas as movimentações
- **Controle** do fluxo de atendimento pelo suporte
- **Validação** interna dos resultados entregues
- **Auditoria** de todas as ações realizadas

---

## 3. Contexto do Problema

Atualmente, quando a equipe interna encontra um problema ou necessita de uma alteração no sistema ERP, uma solicitação é enviada para uma equipe externa de suporte/desenvolvimento.

O processo atual apresenta desafios como:

- **Falta de visibilidade** sobre o status de cada solicitação
- **Dificuldade de rastreamento** do histórico de movimentações
- **Ausência de registro** das datas e responsáveis por cada ação
- **Sem controle formal** sobre resultados de testes e validações
- **Problemas de comunicação** entre as equipes interna e externa

O ControleAtualizacoes visa resolver esses problemas, oferecendo uma ferramenta centralizeda para gerenciar todo o ciclo de vida das solicitações.

---

## 4. Principais Funcionalidades Planejadas

### 4.1 Gestão de Solicitações

- Criação de novas solicitações com registro completo
- Numeração automática sequencial
- Classificação por tipo (alteração, correção, melhoria)
- Definição de prioridade (baixa, média, alta, crítica)
- Registro do solicitante e responsável pelo atendimento
- Associação à equipe/suporte responsável
- Registro da versão do ERP onde o problema foi identificado
- Registro da versão do ERP entregue pelo suporte
- Acompanhamento do status atual
- Registro de datas relevantes
- Campo de observações livres
- Registro do resultado dos testes internos
- Registro de ressalvas quando aplicável

### 4.2 Workflow de Atendimento

- Envio de solicitações ao suporte externo
- Acompanhamento do progresso pelo suporte
- Registro de retornos e Aguardando retorno
- Controle de entregas e versões
- Devolução para novo atendimento quando necessário

### 4.3 Testes e Validação

- Registro de результаdos de testes internos
- Opções de результат: Funcionou, Funcionou com ressalva, Não funcionou, Não foi possível testar
- Registro da descrição da ressalva quando aplicável
- Registro do problema encontrado quando não funciona
- Reabertura de solicitações para novo atendimento

### 4.4 Histórico e Auditoria

- Registro completo de todas as movimentações
- Identificação do usuário que realizou cada ação
- Data e hora de cada movimentação
- Ação realizada e seu contexto
- Status anterior e novo status
- Observações registradas
- Versão do ERP relacionada à movimentação

### 4.5 Segurança e Autenticação

- Autenticação por usuário e senha
- Nome de usuário como identificador de login
- Hash seguro para armazenamento de senhas
- Controle de acesso baseado em permissões

---

## 5. Workflow Planejado

O workflow do sistema contempla os seguintes status:

| Status | Descrição |
|--------|-----------|
| Aberta | Solicitação recém-criada, aguardando envio ao suporte |
| Enviada ao suporte | Solicitação enviada para atendimento externo |
| Em análise | Suporte analizando a solicitação |
| Em desenvolvimento | Suporte trabalhando na implementação |
| Aguardando retorno | Aguardando resposta ou ação de terceiros |
| Entregue | Suporte entregou a solução |
| Em teste | Solução em testes internos |
| Funcionou | Testes internos aprovados |
| Funcionou com ressalva | Testes aprobados com observações |
| Não funcionou | Testes internos reprovados |
| Reaberta | Solicitação reaberta para novo atendimento |
| Cancelada | Solicitação cancelada |
| Encerrada | Solicitação concluída definitivamente |

**Nota:** Este workflow é uma referência inicial e poderá ser refinado durante o desenvolvimento conforme as necessidades reais do processo.

---

## 6. Tecnologias

### Frontend

- **React** - Framework JavaScript para construção de interfaces
- **Vite** - Ferramenta de build e desenvolvimento
- **TypeScript** - Linguagem com tipagem estática

### Backend

- **Laravel** - Framework PHP para desenvolvimento backend
- **PHP** - Linguagem de programação server-side
- **API REST** - Arquitetura de comunicação

### Banco de Dados

- **MySQL** - Sistema de gerenciamento de banco de dados relacional

**Nota:** PostgreSQL não será utilizado neste projeto.

---

## 7. Arquitetura Prevista

```
┌─────────────────────────────────────────────────────────────┐
│                      Frontend                                │
│                   React + Vite + TypeScript                  │
└─────────────────────────────────────────────────────────────┘
                              │
                              │ HTTP/REST
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                      API REST                               │
│                   Laravel + PHP                             │
└─────────────────────────────────────────────────────────────┘
                              │
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                     Banco de Dados                          │
│                        MySQL                                │
└─────────────────────────────────────────────────────────────┘
```

A aplicação é baseada em uma arquitetura cliente-servidor, com frontend e backend separados, communicating através de uma API REST.

---

## 8. Estrutura Planejada do Projeto

```
ControleAtualizacoes/
├── frontend/                 # Aplicação React (Vite + TypeScript)
│   ├── src/
│   │   ├── components/     # Componentes React
│   │   ├── pages/          # Páginas da aplicação
│   │   ├── services/       # Serviços de comunicação com API
│   │   ├── contexts/       # Contextos React (autenticação, etc.)
│   │   ├── hooks/          # Custom hooks
│   │   ├── types/          # Definições TypeScript
│   │   └── utils/          # Funções utilities
│   ├── public/             # Arquivos públicos
│   └── package.json        # Dependências frontend
│
├── backend/                  # Aplicação Laravel (PHP)
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/  # Controladores da API
│   │   │   ├── Middleware/   # Middlewares
│   │   │   └── Requests/     # Validação de requests
│   │   ├── Models/          # Modelos Eloquent
│   │   ├── Policies/        # Políticas de autorização
│   │   └── Providers/       # Provedores de serviços
│   ├── database/
│   │   └── migrations/      # Migrations do banco de dados
│   ├── routes/
│   │   └── api.php          # Rotas da API
│   └── composer.json        # Dependências backend
│
└── README.md                # Documentação do projeto
```

**Nota:** A estrutura final poderá ser ajustada durante o desenvolvimento.

---

## 9. Requisitos Futuros

Esta seção documenta os requisitos identificados que serão implementados nas próximas fases:

### 9.1 Requisitos Funcionais

- [ ] Cadastro e autenticação de usuários
- [ ] CRUD completo de solicitações
- [ ] Workflow de atendimento com múltiplos status
- [ ] Registro de histórico de movimentações
- [ ] Sistema de testes e resultados
- [ ] Gestão de ressalvas
- [ ] Reabertura de solicitações
- [ ] Busca e filtros de solicitações
- [ ] Dashboard com indicadores

### 9.2 Requisitos Não Funcionais

- [ ] Interface responsiva
- [ ] Performance adequada para uso simultâneo
- [ ] Segurança na autenticação e autorização
- [ ] Backup regular do banco de dados
- [ ] Logs de auditoria

---

## 10. Instalação Planejada

> **Status:** A seção de instalação será preenchida/atualizada conforme o desenvolvimento avançar.

As instruções detalhadas de instalação serão documentadas após a conclusão das fases de estrutura base do projeto, frontend e backend.

### Requisitos Previstos

- Node.js (versão a ser definida)
- PHP (versão compatí vel com Laravel a ser definida)
- Composer
- MySQL (versão a ser definida)
- Git

### Passos de Instalação

1. Clonar o repositório
2. Instalar dependências do backend (Composer)
3. Instalar dependências do frontend (npm/pnpm)
4. Configurar variáveis de ambiente
5. Executar migrations do banco de dados
6. Iniciar os servidores de desenvolvimento

---

## 11. Configuração Planejada

> **Status:** A seção de configuração será preenchida/atualizada conforme o desenvolvimento avançar.

### Variáveis de Ambiente (Backend)

```
APP_NAME=ControleAtualizacoes
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=controle_atualizacoes
DB_USERNAME=root
DB_PASSWORD=

CORS_ALLOWED_ORIGINS=http://localhost:5173
```

### Variáveis de Ambiente (Frontend)

```
VITE_API_BASE_URL=http://localhost:8000/api
```

---

## 12. Execução em Ambiente de Desenvolvimento

> **Status:** Os comandos de execução serão documentados após a conclusão das fases de estrutura base do projeto.

### Backend (Laravel)

```bash
cd backend
php artisan serve
```

### Frontend (Vite)

```bash
cd frontend
npm run dev
```

---

## 13. Banco de Dados

### Sistema de Banco

- **MySQL** - Sistema de gerenciamento de banco de dados relacional

**Nota:** PostgreSQL não será utilizado neste projeto.

### Principais Entidades

#### Usuários

Armazenará informações dos usuários do sistema.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | Identificador único |
| username | varchar | Nome de usuário para login |
| password | varchar | Hash da senha |
| nome | varchar | Nome completo |
| email | varchar | E-mail (opcional) |
| ativo | boolean | Status do usuário |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

#### Solicitações

Armazenará as solicitações de alteração.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | Identificador único |
| numero | varchar | Número da solicitação |
| titulo | varchar | Título da solicitação |
| descricao | text | Descrição detalhada |
| tipo | varchar | Tipo: alteração, correção, melhoria |
| prioridade | varchar | Prioridade: baixa, média, alta, crítica |
| solicitante_id | bigint | ID do usuário solicitante |
| responsavel_id | bigint | ID do responsável pelo atendimento |
| equipe | varchar | Equipe/suporte responsável |
| versao_erp_identificado | varchar | Versão onde o problema foi identificado |
| versao_erp_entregue | varchar | Versão entregue pelo suporte |
| status | varchar | Status atual da solicitação |
| resultado_teste | varchar | Resultado dos testes |
| ressalva | text | Descrição da ressalva |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

#### Movimentações (Histórico)

Armazenará o histórico completo de movimentações.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | Identificador único |
| solicitacao_id | bigint | ID da solicitação relacionada |
| usuario_id | bigint | ID do usuário que realizou a ação |
| acao | varchar | Ação realizada |
| status_anterior | varchar | Status anterior |
| novo_status | varchar | Novo status |
| observacao | text | Observação registrada |
| versao_erp | varchar | Versão relacionada à movimentação |
| created_at | timestamp | Data e hora da movimentação |

### Migrations

As migrations do banco de dados serão criadas na **Fase 1** do projeto.

---

## 14. Autenticação

### Sistema de Login

- Autenticação utilizando **nome de usuário** e **senha**
- O **nome de usuário** será utilizado como identificador para login
- **E-mail NÃO será utilizado como login**
- Senhas armazenadas utilizando **hash seguro** (bcrypt ou argon2)

### Fluxo de Autenticação

1. Usuário acessa a página de login
2. Insere nome de usuário e senha
3. Sistema valida as credenciais
4. Em caso de sucesso, sistema gera token de sessão
5. Token é utilizado para autenticar requisições subsequentes

### Segurança

- Senhas nunca são armazenadas em texto plano
- Utilização de algoritmos de hash seguros
- Proteção contra ataques de força bruta (rate limiting)
- Sessões com tempo de expiração

---

## 15. Estrutura de Usuários e Permissões

> **Status:** A estrutura de permissões será definida em detalhes na Fase 2.

### Perfis Previstos

| Perfil | Descrição |
|--------|-----------|
| Administrador | Acesso completo ao sistema |
| Gestor | Gestión de solicitudes y approval |
| Solicitante | Pode criar e acompanhar solicitudes propias |
| Suporte | Atendimento e atualização de solicitudes |

### Permissões Planejadas

- **Criar solicitação:** Permissão para abrir novas solicitudes
- **Editar solicitação:** Permissão para editar solicitudes
- **Alterar status:** Permissão para mudar o status de uma solicitação
- **Registrar teste:** Permissão para registrar resultados de testes
- **Visualizar histórico:** Permissão para consultar o histórico completo
- **Gerenciar usuários:** Permissão para cadastrar e editar usuários
- **Relatórios:** Permissão para acessar relatórios e dashboards

---

## 16. Controle de Solicitações

### Campos de uma Solicitação

Cada solicitação poderá futuramente conter os seguintes campos:

| Campo | Descrição |
|-------|-----------|
| Número | Identificador único e sequencial |
| Título | Nome ou resumo da solicitação |
| Descrição | Descrição detalhada do problema ou melhoria |
| Tipo | Classificação: alteração, correção, melhoria |
| Prioridade | Nível de urgência: baixa, média, alta, crítica |
| Solicitante | Usuário que abriu a solicitação |
| Responsável | Pessoa responsável pelo atendimento |
| Equipe | Equipe/suporte externo responsável |
| Versão ERP Identificado | Versão do ERP onde o problema foi identificado |
| Versão ERP Entregue | Versão do ERP entregue pelo suporte |
| Status | Status atual da solicitação |
| Data de Abertura | Data e hora da abertura |
| Data de Entrega | Data e hora da entrega |
| Observações | Campo livre para observações |
| Resultado do Teste | Resultado dos testes internos |
| Ressalva | Descrição da ressalva quando aplicável |

### Versão do ERP

**Importante:** A versão do ERP **não possui formato fixo**.

A versão pode ser baseada em data ou qualquer outro formato utilizado pela empresa.

**Exemplos de formatos possíveis:**

- `2026.09.04`
- `04/09/2026`
- `2026-09-04`
- `Versão Setembro/2026`
- `Qualquer outro texto informado pelo usuário`

**Não serão criadas regras de validação que obriguem um padrão específico de versão.** O campo será tratado como **texto livre**.

---

## 17. Histórico e Auditoria

O histórico é uma das principais características do sistema, permitindo reconstruir toda a história de uma solicitação.

### Dados Registrados no Histórico

Para cada movimentação, o sistema registrará:

| Campo | Descrição |
|-------|-----------|
| Usuário | Quem realizou a ação |
| Data/Hora | Quando a ação foi realizada |
| Ação | Qual ação foi realizada |
| Status Anterior | Status antes da mudança |
| Novo Status | Status após a mudança |
| Observação | Observação registrada na ação |
| Versão ERP | Versão relacionada à movimentação (quando aplicável) |

### Objetivos do Histórico

- **Rastreabilidade:** Permitir saber o que aconteceu, quando aconteceu e quem realizou cada ação
- **Auditoria:** Manter registro completo para futuras auditorias
- **Comunicação:** Facilitar o acompanhamento por todas as partes envolvidas
- **Transparência:** Disponibilizar informações completas sobre cada solicitação

---

## 18. Versionamento

O projeto segue o seguinte padrão de versionamento semântico:

**Formato:** `MAJOR.MINOR.PATCH`

- **MAJOR:** Incrementado quando há mudanças incompatíveis na API
- **MINOR:** Incrementado quando há novas funcionalidades compatíveis
- **PATCH:** Incrementado quando há correções de bugs compatíveis

### Controle de Versão do Código

O código fonte será versionado utilizando **Git** e armazenado no **GitHub**.

Repositório: https://github.com/fortluzti/ControleAtualizacoes

---

## 19. Roadmap

### Visão Geral das Fases

| Fase | Descrição | Status |
|------|-----------|--------|
| 0 | Documentação inicial | ✅ Atual |
| 1 | Estrutura base do projeto | ⏳ Pendente |
| 2 | Autenticação e usuários | ⏳ Pendente |
| 3 | Cadastro e controle de solicitações | ⏳ Pendente |
| 4 | Atendimento do suporte | ⏳ Pendente |
| 5 | Testes, resultados e ressalvas | ⏳ Pendente |
| 6 | Histórico e auditoria | ⏳ Pendente |
| 7 | Dashboard e indicadores | ⏳ Pendente |
| 8 | Anexos, melhorias e refinamentos | ⏳ Pendente |

### Detalhamento das Fases

#### Fase 0 — Documentação Inicial (Atual)

- [x] Criação do README.md
- [x] Documentação do contexto e objetivos
- [x] Definição das tecnologias
- [x] Planejamento da arquitetura
- [x] Elaboração do roadmap inicial
- [x] Definição do workflow planejado

#### Fase 1 — Estrutura Base do Projeto

- [ ] Configuração do projeto frontend (React + Vite + TypeScript)
- [ ] Configuração do projeto backend (Laravel)
- [ ] Configuração do banco de dados (MySQL)
- [ ] Criação das migrations iniciais
- [ ] Configuração da API REST
- [ ] Estrutura básica de diretórios

#### Fase 2 — Autenticação e Usuários

- [ ] Sistema de login (usuário e senha)
- [ ] Hash seguro de senhas
- [ ] CRUD de usuários
- [ ] Controle de sessões
- [ ] Middleware de autenticação
- [ ] Perfis e permissões básicos

#### Fase 3 — Cadastro e Controle de Solicitações

- [ ] CRUD completo de solicitações
- [ ] Numeração automática
- [ ] Campos configuráveis
- [ ] Validações de entrada
- [ ] Listagem e filtros
- [ ] Busca de solicitações

#### Fase 4 — Atendimento do Suporte

- [ ] Workflow de envio ao suporte
- [ ] Registro de status de atendimento
- [ ] Controle de versões entregues
- [ ] Comunicação de retornos
- [ ] Gestão de equipe/suporte

#### Fase 5 — Testes, Resultados e Ressalvas

- [ ] Registro de resultados de testes
- [ ] Opções: Funcionou, Funcionou com ressalva, Não funcionou, Não foi possível testar
- [ ] Registro de ressalvas
- [ ] Registro de problemas encontrados
- [ ] Reabertura de solicitações

#### Fase 6 — Histórico e Auditoria

- [ ] Registro completo de movimentações
- [ ] Visualização do histórico por solicitação
- [ ] Auditoria de ações
- [ ] Busca no histórico
- [ ] Relatórios de movimentação

#### Fase 7 — Dashboard e Indicadores

- [ ] Dashboard principal
- [ ] Indicadores de solicitações
- [ ] Gráficos e visualizações
- [ ] Filtros por período
- [ ] Exportação de dados

#### Fase 8 — Anexos, Melhorias e Refinamentos

- [ ] Upload de anexos
- [ ] Melhorias de interface
- [ ] Refinamentos de UX
- [ ] Otimizações de performance
- [ ] Correções finais

**Nota:** O roadmap poderá ser atualizado conforme o desenvolvimento avançar e novas necessidades forem identificadas.

---

## 20. Estratégia de Desenvolvimento por Fases

O projeto será desenvolvido de forma incremental, seguindo uma estratégia de fases bem definidas:

### Princípios

1. **Documentação Primeiro:** Cada fase começa com revisão da documentação
2. **Código Limpo:** Prioridade em código bem estruturado e documentado
3. **Testes:** Cada funcionalidade deve ser testada antes de avançar
4. **Commits Atômicos:** Cada commit deve representar uma unidade lógica de trabalho
5. **Revisões:** Revisão de código antes da integração

### Critérios de Conclusão de Fase

Para que uma fase seja considerada concluída:

1. Toda a documentação prevista está atualizada
2. Todas as funcionalidades planejadas estão implementadas
3. O código foi revisado e testado
4. Os commits foram realizados
5. O push foi feito para o repositório remoto

### Progressão Entre Fases

- Cada fase será revisada antes de iniciar a próxima
- Problemas identificados devem ser corrigidos antes de prosseguir
- A documentação deve refletir o estado atual do projeto

---

## 21. Regras Importantes do Projeto

### 21.1 Versão do ERP

⚠️ **IMPORTANTE:** A versão do ERP **NÃO** possui formato fixo.

- Não assumir que a versão será algo como `5.82.17`
- A versão pode ser baseada em data ou qualquer outro formato
- Todos os campos relacionados à versão do ERP devem ser tratados como **TEXTO LIVRE**
- **Não criar regras de validação que obriguem um padrão específico de versão**

### 21.2 Autenticação

- Login é feito com **nome de usuário**, não com e-mail
- Senhas devem ser armazenadas utilizando **hash seguro**
- E-mail **não** será utilizado como login

### 21.3 Banco de Dados

- Utilizar **MySQL** neste projeto
- **Não utilizar PostgreSQL**

### 21.4 Tecnologias Definidas

| Camada | Tecnologia |
|--------|------------|
| Frontend | React + Vite + TypeScript |
| Backend | Laravel + PHP + API REST |
| Banco | MySQL |

### 21.5 Escopo da Fase 0

Na **Fase 0** (atual):
- ✅ Criar documentação
- ❌ Não criar código
- ❌ Não criar frontend
- ❌ Não criar backend
- ❌ Não criar banco de dados
- ❌ Não instalar dependências

### 21.6 Padrão de Commits

Os commits devem seguir um padrão consistente:

```
<tipo>: <descrição breve>

[descrição mais detalhada se necessário]
```

Exemplos:
- `docs: cria documentacao inicial do projeto`
- `feat: implementa autenticacao de usuarios`
- `fix: corrige validacao de campos`

---

## 22. Créditos

### Desenvolvedor

**Desenvolvido por Alex Fabiano Longo.**

### Créditos na Aplicação

Futuramente, o nome **Alex Fabiano Longo** deverá aparecer de maneira discreta e profissional nas telas da aplicação, por exemplo:

- No rodapé da aplicação
- Na área de informações do sistema
- Em tela "Sobre" ou equivalente

**Não serão adicionados créditos exageradamente em todas as telas.** A presença será discreta e profissional.

---

## 23. Licença

> **Nota:** A licença definitiva do projeto ainda poderá ser definida posteriormente.

Este projeto está em desenvolvimento. A licença final será definida antes do lançamento da primeira versão estável.

Enquanto a licença definitiva não for definida, todos os direitos são reservados ao autor.

---

## 24. Informações Adicionais

### Repositório

- **GitHub:** https://github.com/fortluzti/ControleAtualizacoes
- **Branch Principal:** main

### Contato

Para mais informações ou dúvidas sobre o projeto, entre em contato com o desenvolvedor.

---

*Última atualização: Documentação inicial - Fase 0*
