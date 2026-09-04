/**
 * Página de Visualização de Solicitação
 */

import { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { solicitacaoService, type Solicitacao } from '../services/solicitacao';
import { useAuth } from '../contexts/AuthContext';
import './FormularioSolicitacaoPage.css';

export function VisualizarSolicitacaoPage() {
  const { id } = useParams<{ id: string }>();
  const [solicitacao, setSolicitacao] = useState<Solicitacao | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const { user, logout } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    loadSolicitacao();
  }, [id]);

  const loadSolicitacao = async () => {
    if (!id) return;

    setLoading(true);
    setError('');

    const result = await solicitacaoService.get(parseInt(id, 10));

    setLoading(false);

    if (result.error) {
      setError(result.error);
    } else if (result.data) {
      setSolicitacao(result.data);
    }
  };

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
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

  if (loading) {
    return (
      <div className="formulario-container">
        <div className="loading-state">Carregando...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="formulario-container">
        <header className="header">
          <div className="header-left">
            <h1>Solicitação</h1>
          </div>
          <div className="header-right">
            <span className="user-info">
              {user?.nome} ({user?.perfil})
            </span>
            <button onClick={handleLogout} className="btn-logout">Sair</button>
          </div>
        </header>
        <nav className="nav-menu">
          <Link to="/home" className="nav-link">Início</Link>
          <Link to="/solicitacoes" className="nav-link">Solicitações</Link>
        </nav>
        <main className="main-content">
          <div className="error-message">{error}</div>
        </main>
      </div>
    );
  }

  if (!solicitacao) {
    return (
      <div className="formulario-container">
        <div className="empty-state">Solicitação não encontrada</div>
      </div>
    );
  }

  return (
    <div className="formulario-container">
      <header className="header">
        <div className="header-left">
          <h1>Solicitação {solicitacao.numero}</h1>
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
        <Link to="/solicitacoes" className="nav-link">Solicitações</Link>
        <Link to={`/solicitacoes/${id}`} className="nav-link active">Visualizar</Link>
      </nav>

      <main className="main-content">
        <div className="view-section">
          <div className="view-header">
            <h2>{solicitacao.titulo}</h2>
            <div className="view-actions">
              <Link to={`/solicitacoes/${id}/editar`} className="btn-edit">
                ✏️ Editar
              </Link>
              <Link to="/solicitacoes" className="btn-voltar">
                ← Voltar
              </Link>
            </div>
          </div>

          <div className="view-badges">
            <span className={`badge ${getPrioridadeClass(solicitacao.prioridade)}`}>
              {solicitacao.prioridade_label}
            </span>
            <span className={`badge-status ${getStatusClass(solicitacao.status)}`}>
              {solicitacao.status_label}
            </span>
            <span className="badge-tipo">{solicitacao.tipo_label}</span>
          </div>

          <div className="view-grid">
            <div className="view-field">
              <label>Número</label>
              <span className="field-value numero">{solicitacao.numero}</span>
            </div>
            <div className="view-field">
              <label>Versão do ERP</label>
              <span className="field-value">{solicitacao.versao_erp}</span>
            </div>
            <div className="view-field">
              <label>Solicitante</label>
              <span className="field-value">{solicitacao.solicitante?.nome ?? '-'}</span>
            </div>
            <div className="view-field">
              <label>Criado em</label>
              <span className="field-value">{formatDate(solicitacao.created_at)}</span>
            </div>
            <div className="view-field">
              <label>Última atualização</label>
              <span className="field-value">{formatDate(solicitacao.updated_at)}</span>
            </div>
          </div>

          <div className="view-field full-width">
            <label>Descrição</label>
            <div className="field-textarea">{solicitacao.descricao}</div>
          </div>

          {solicitacao.observacoes && (
            <div className="view-field full-width">
              <label>Observações</label>
              <div className="field-textarea">{solicitacao.observacoes}</div>
            </div>
          )}
        </div>
      </main>

      <footer className="footer">
        <p>Desenvolvido por Alex Fabiano Longo</p>
      </footer>
    </div>
  );
}

export default VisualizarSolicitacaoPage;
