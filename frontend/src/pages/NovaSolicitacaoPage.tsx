/**
 * Página de Nova Solicitação
 */

import { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { solicitacaoService, type SolicitacaoCreate } from '../services/solicitacao';
import { useAuth } from '../contexts/AuthContext';
import './FormularioSolicitacaoPage.css';

export function NovaSolicitacaoPage() {
  const [formData, setFormData] = useState<SolicitacaoCreate>({
    titulo: '',
    descricao: '',
    tipo: 'CORRECAO',
    prioridade: 'NORMAL',
    versao_erp: '',
    observacoes: '',
  });
  const [errors, setErrors] = useState<string[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [opcoes, setOpcoes] = useState<{ tipos: Record<string, string>; prioridades: Record<string, string> } | null>(null);

  const { user, logout } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    loadOpcoes();
  }, []);

  const loadOpcoes = async () => {
    const result = await solicitacaoService.opcoes();
    if (result.data) {
      setOpcoes({
        tipos: result.data.tipos,
        prioridades: result.data.prioridades,
      });
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrors([]);
    setIsLoading(true);

    const result = await solicitacaoService.create(formData);

    setIsLoading(false);

    if (result.errors) {
      setErrors(result.errors);
    } else if (result.error) {
      setErrors([result.error]);
    } else if (result.data) {
      navigate(`/solicitacoes/${result.data.id}`);
    }
  };

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <div className="formulario-container">
      <header className="header">
        <div className="header-left">
          <h1>Nova Solicitação</h1>
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
        <Link to="/solicitacoes/nova" className="nav-link active">Nova Solicitação</Link>
      </nav>

      <main className="main-content">
        <div className="form-section">
          <div className="form-header">
            <h2>Dados da Solicitação</h2>
            <Link to="/solicitacoes" className="btn-voltar">
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
                value={formData.titulo}
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
                value={formData.descricao}
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
                  value={formData.tipo}
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
                  value={formData.prioridade}
                  onChange={handleChange}
                  disabled={isLoading}
                >
                  {opcoes?.prioridades && Object.entries(opcoes.prioridades).map(([value, label]) => (
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
                value={formData.versao_erp}
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
                value={formData.observacoes}
                onChange={handleChange}
                placeholder="Informações adicionais (opcional)"
                rows={4}
                disabled={isLoading}
                maxLength={5000}
              />
            </div>

            <div className="form-actions">
              <button type="submit" className="btn-submit" disabled={isLoading}>
                {isLoading ? 'Criando...' : 'Criar Solicitação'}
              </button>
              <Link to="/solicitacoes" className="btn-cancel">
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

export default NovaSolicitacaoPage;
