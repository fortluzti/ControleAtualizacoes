/**
 * Página de Edição de Solicitação
 */

import { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { solicitacaoService, type Solicitacao, type SolicitacaoUpdate } from '../services/solicitacao';
import { useAuth } from '../contexts/AuthContext';
import './FormularioSolicitacaoPage.css';

export function EditarSolicitacaoPage() {
  const { id } = useParams<{ id: string }>();
  const [solicitacao, setSolicitacao] = useState<Solicitacao | null>(null);
  const [formData, setFormData] = useState<SolicitacaoUpdate>({});
  const [loading, setLoading] = useState(true);
  const [errors, setErrors] = useState<string[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [opcoes, setOpcoes] = useState<{ tipos: Record<string, string>; prioridades: Record<string, string>; status: Record<string, string> } | null>(null);

  const { user, logout } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    loadData();
  }, [id]);

  const loadData = async () => {
    if (!id) return;

    setLoading(true);

    const [solicitacaoResult, opcoesResult] = await Promise.all([
      solicitacaoService.get(parseInt(id, 10)),
      solicitacaoService.opcoes(),
    ]);

    setLoading(false);

    if (solicitacaoResult.error) {
      setErrors([solicitacaoResult.error]);
      return;
    }

    if (solicitacaoResult.data) {
      setSolicitacao(solicitacaoResult.data);
      setFormData({
        titulo: solicitacaoResult.data.titulo,
        descricao: solicitacaoResult.data.descricao,
        tipo: solicitacaoResult.data.tipo,
        prioridade: solicitacaoResult.data.prioridade,
        status: solicitacaoResult.data.status,
        versao_erp: solicitacaoResult.data.versao_erp,
        observacoes: solicitacaoResult.data.observacoes ?? '',
      });
    }

    if (opcoesResult.data) {
      setOpcoes(opcoesResult.data);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!id) return;
    
    setErrors([]);
    setIsLoading(true);

    const result = await solicitacaoService.update(parseInt(id, 10), formData);

    setIsLoading(false);

    if (result.errors) {
      setErrors(result.errors);
    } else if (result.error) {
      setErrors([result.error]);
    } else if (result.data) {
      navigate(`/solicitacoes/${id}`);
    }
  };

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  if (loading) {
    return (
      <div className="formulario-container">
        <div className="loading-state">Carregando...</div>
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
          <h1>Editar Solicitação</h1>
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
        <Link to={`/solicitacoes/${id}`} className="nav-link">Visualizar</Link>
        <Link to={`/solicitacoes/${id}/editar`} className="nav-link active">Editar</Link>
      </nav>

      <main className="main-content">
        <div className="form-section">
          <div className="form-header">
            <h2>{solicitacao.numero}</h2>
            <Link to={`/solicitacoes/${id}`} className="btn-voltar">
              ← Voltar
            </Link>
          </div>

          {errors.length > 0 && (
            <div className="error-message">
              <ul>
                {errors.map((error, index) => (
                  <li key={index}>{error}</li>
                ))}
              </ul>
            </div>
          )}

          <form onSubmit={handleSubmit} className="solicitacao-form">
            <div className="form-group">
              <label htmlFor="titulo">Título <span className="required">*</span></label>
              <input
                type="text"
                id="titulo"
                name="titulo"
                value={formData.titulo ?? ''}
                onChange={handleChange}
                placeholder="Resumo da solicitação"
                disabled={isLoading}
                maxLength={255}
              />
            </div>

            <div className="form-group">
              <label htmlFor="descricao">Descrição <span className="required">*</span></label>
              <textarea
                id="descricao"
                name="descricao"
                value={formData.descricao ?? ''}
                onChange={handleChange}
                placeholder="Descreva detalhadamente a solicitação..."
                rows={6}
                disabled={isLoading}
              />
            </div>

            <div className="form-row">
              <div className="form-group">
                <label htmlFor="tipo">Tipo <span className="required">*</span></label>
                <select
                  id="tipo"
                  name="tipo"
                  value={formData.tipo ?? ''}
                  onChange={handleChange}
                  disabled={isLoading}
                >
                  {opcoes?.tipos && Object.entries(opcoes.tipos).map(([value, label]) => (
                    <option key={value} value={value}>{label}</option>
                  ))}
                </select>
              </div>

              <div className="form-group">
                <label htmlFor="prioridade">Prioridade <span className="required">*</span></label>
                <select
                  id="prioridade"
                  name="prioridade"
                  value={formData.prioridade ?? ''}
                  onChange={handleChange}
                  disabled={isLoading}
                >
                  {opcoes?.prioridades && Object.entries(opcoes.prioridades).map(([value, label]) => (
                    <option key={value} value={value}>{label}</option>
                  ))}
                </select>
              </div>

              <div className="form-group">
                <label htmlFor="status">Status</label>
                <select
                  id="status"
                  name="status"
                  value={formData.status ?? ''}
                  onChange={handleChange}
                  disabled={isLoading}
                >
                  {opcoes?.status && Object.entries(opcoes.status).map(([value, label]) => (
                    <option key={value} value={value}>{label}</option>
                  ))}
                </select>
              </div>
            </div>

            <div className="form-group">
              <label htmlFor="versao_erp">Versão do ERP <span className="required">*</span></label>
              <input
                type="text"
                id="versao_erp"
                name="versao_erp"
                value={formData.versao_erp ?? ''}
                onChange={handleChange}
                placeholder="Ex: 2026.09.04, Build 2026-09-04, ERP Setembro 2026"
                disabled={isLoading}
                maxLength={50}
              />
              <small className="hint">Informe a versão ou build do ERP (texto livre)</small>
            </div>

            <div className="form-group">
              <label htmlFor="observacoes">Observações</label>
              <textarea
                id="observacoes"
                name="observacoes"
                value={formData.observacoes ?? ''}
                onChange={handleChange}
                placeholder="Informações adicionais (opcional)"
                rows={4}
                disabled={isLoading}
                maxLength={5000}
              />
            </div>

            <div className="info-box">
              <p><strong>Solicitante:</strong> {solicitacao.solicitante?.nome ?? '-'}</p>
              <p><strong>Criado em:</strong> {new Date(solicitacao.created_at).toLocaleDateString('pt-BR')}</p>
            </div>

            <div className="form-actions">
              <button type="submit" className="btn-submit" disabled={isLoading}>
                {isLoading ? 'Salvando...' : 'Salvar Alterações'}
              </button>
              <Link to={`/solicitacoes/${id}`} className="btn-cancel">
                Cancelar
              </Link>
            </div>
          </form>
        </div>
      </main>

      <footer className="footer">
        <p>Desenvolvido por Alex Fabiano Longo</p>
      </footer>
    </div>
  );
}

export default EditarSolicitacaoPage;
