/**
 * Página de Lista de Solicitações
 */

import { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { solicitacaoService, type Solicitacao } from '../services/solicitacao';
import { useAuth } from '../contexts/AuthContext';
import './ListaSolicitacoesPage.css';

export function ListaSolicitacoesPage() {
  const [solicitacoes, setSolicitacoes] = useState<Solicitacao[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  
  // Filtros
  const [filtroNumero, setFiltroNumero] = useState('');
  const [filtroTitulo, setFiltroTitulo] = useState('');
  const [filtroStatus, setFiltroStatus] = useState('');
  const [filtroPrioridade, setFiltroPrioridade] = useState('');
  
  // Opções para selects
  const [opcoes, setOpcoes] = useState<{ tipos: Record<string, string>; prioridades: Record<string, string>; status: Record<string, string> } | null>(null);
  
  // Paginação
  const [pagination, setPagination] = useState({ page: 1, per_page: 20, total: 0, total_pages: 0 });
  
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  // Carregar opções e solicitações
  useEffect(() => {
    loadOpcoes();
    loadSolicitacoes();
  }, []);

  const loadOpcoes = async () => {
    const result = await solicitacaoService.opcoes();
    if (result.data) {
      setOpcoes(result.data);
    }
  };

  const loadSolicitacoes = async (page = 1) => {
    setLoading(true);
    setError('');

    const result = await solicitacaoService.list({
      numero: filtroNumero || undefined,
      titulo: filtroTitulo || undefined,
      status: filtroStatus || undefined,
      prioridade: filtroPrioridade || undefined,
      page,
      per_page: pagination.per_page,
    });

    setLoading(false);

    if (result.error) {
      setError(result.error);
    } else if (result.data) {
      setSolicitacoes(result.data.data);
      setPagination({
        ...pagination,
        page: result.data.pagination.page,
        total: result.data.pagination.total,
        total_pages: result.data.pagination.total_pages,
      });
    }
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    loadSolicitacoes(1);
  };

  const handleClearFilters = () => {
    setFiltroNumero('');
    setFiltroTitulo('');
    setFiltroStatus('');
    setFiltroPrioridade('');
    setTimeout(() => loadSolicitacoes(1), 0);
  };

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  const getPrioridadeClass = (prioridade: string): string => {
    switch (prioridade) {
      case 'URGENTE': return 'prioridade-urgente';
      case 'ALTA': return 'prioridade-alta';
      case 'NORMAL': return 'prioridade-normal';
      case 'BAIXA': return 'prioridade-baixa';
      default: return '';
    }
  };

  const getStatusClass = (status: string): string => {
    switch (status) {
      case 'ABERTA': return 'status-aberta';
      case 'REABERTA': return 'status-reaberta';
      case 'CANCELADA': return 'status-cancelada';
      case 'ENCERRADA': return 'status-encerrada';
      case 'FUNCIONOU': return 'status-funcionou';
      case 'NAO_FUNCIONOU': return 'status-nao-funcionou';
      default: return 'status-em-andamento';
    }
  };

  const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    });
  };

  return (
    <div className="lista-container">
      <header className="header">
        <div className="header-left">
          <h1>Solicitações</h1>
        </div>
        <div className="header-right">
          <span className="user-info">
            {user?.nome} ({user?.perfil})
          </span>
          <button onClick={handleLogout} className="btn-logout">
            Sair
          </button>
        </div>
      </header>

      <nav className="nav-menu">
        <Link to="/home" className="nav-link">Início</Link>
        <Link to="/solicitacoes" className="nav-link active">Solicitações</Link>
        <Link to="/solicitacoes/nova" className="nav-link">Nova Solicitação</Link>
      </nav>

      <main className="main-content">
        <div className="filters-section">
          <h2>Filtros</h2>
          <form onSubmit={handleSearch} className="filters-form">
            <div className="filter-row">
              <div className="filter-group">
                <label htmlFor="filtro-numero">Número</label>
                <input
                  type="text"
                  id="filtro-numero"
                  value={filtroNumero}
                  onChange={(e) => setFiltroNumero(e.target.value)}
                  placeholder="Ex: SOL-000001"
                />
              </div>
              <div className="filter-group flex-grow">
                <label htmlFor="filtro-titulo">Título</label>
                <input
                  type="text"
                  id="filtro-titulo"
                  value={filtroTitulo}
                  onChange={(e) => setFiltroTitulo(e.target.value)}
                  placeholder="Buscar por título..."
                />
              </div>
              <div className="filter-group">
                <label htmlFor="filtro-status">Status</label>
                <select
                  id="filtro-status"
                  value={filtroStatus}
                  onChange={(e) => setFiltroStatus(e.target.value)}
                >
                  <option value="">Todos</option>
                  {opcoes?.status && Object.entries(opcoes.status).map(([value, label]) => (
                    <option key={value} value={value}>{label}</option>
                  ))}
                </select>
              </div>
              <div className="filter-group">
                <label htmlFor="filtro-prioridade">Prioridade</label>
                <select
                  id="filtro-prioridade"
                  value={filtroPrioridade}
                  onChange={(e) => setFiltroPrioridade(e.target.value)}
                >
                  <option value="">Todas</option>
                  {opcoes?.prioridades && Object.entries(opcoes.prioridades).map(([value, label]) => (
                    <option key={value} value={value}>{label}</option>
                  ))}
                </select>
              </div>
            </div>
            <div className="filter-actions">
              <button type="submit" className="btn-search">
                Buscar
              </button>
              <button type="button" onClick={handleClearFilters} className="btn-clear">
                Limpar
              </button>
            </div>
          </form>
        </div>

        {error && <div className="error-message">{error}</div>}

        <div className="table-section">
          <div className="table-header">
            <h2>Solicitações ({pagination.total})</h2>
            <Link to="/solicitacoes/nova" className="btn-nova">
              Nova Solicitação
            </Link>
          </div>

          {loading ? (
            <div className="loading">Carregando...</div>
          ) : solicitacoes.length === 0 ? (
            <div className="empty-state">
              <p>Nenhuma solicitação encontrada.</p>
            </div>
          ) : (
            <>
              <table className="solicitacoes-table">
                <thead>
                  <tr>
                    <th>Número</th>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Prioridade</th>
                    <th>Status</th>
                    <th>Versão</th>
                    <th>Solicitante</th>
                    <th>Data</th>
                    <th>Ações</th>
                  </tr>
                </thead>
                <tbody>
                  {solicitacoes.map((sol) => (
                    <tr key={sol.id}>
                      <td className="cell-numero">{sol.numero}</td>
                      <td className="cell-titulo">{sol.titulo}</td>
                      <td>{sol.tipo_label}</td>
                      <td>
                        <span className={`badge ${getPrioridadeClass(sol.prioridade)}`}>
                          {sol.prioridade_label}
                        </span>
                      </td>
                      <td>
                        <span className={`badge-status ${getStatusClass(sol.status)}`}>
                          {sol.status_label}
                        </span>
                      </td>
                      <td>{sol.versao_erp}</td>
                      <td>{sol.solicitante?.nome ?? '-'}</td>
                      <td>{formatDate(sol.created_at)}</td>
                      <td className="cell-actions">
                        <Link to={`/solicitacoes/${sol.id}`} className="btn-action" title="Visualizar">
                          👁️
                        </Link>
                        <Link to={`/solicitacoes/${sol.id}/editar`} className="btn-action" title="Editar">
                          ✏️
                        </Link>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>

              {pagination.total_pages > 1 && (
                <div className="pagination">
                  <button
                    onClick={() => loadSolicitacoes(pagination.page - 1)}
                    disabled={pagination.page <= 1}
                    className="btn-pagination"
                  >
                    Anterior
                  </button>
                  <span className="pagination-info">
                    Página {pagination.page} de {pagination.total_pages}
                  </span>
                  <button
                    onClick={() => loadSolicitacoes(pagination.page + 1)}
                    disabled={pagination.page >= pagination.total_pages}
                    className="btn-pagination"
                  >
                    Próxima
                  </button>
                </div>
              )}
            </>
          )}
        </div>
      </main>

      <footer className="footer">
        <p>Desenvolvido por Alex Fabiano Longo</p>
      </footer>
    </div>
  );
}

export default ListaSolicitacoesPage;
