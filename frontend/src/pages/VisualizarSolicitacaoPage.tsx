/**
 * Página de Visualização de Solicitação
 */

import { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { solicitacaoService, type Solicitacao, type Historico, type ResponsavelSuporte } from '../services/solicitacao';
import responsaveisService from '../services/responsaveis';
import { useAuth } from '../contexts/AuthContext';
import './VisualizarSolicitacaoPage.css';

export function VisualizarSolicitacaoPage() {
  const { id } = useParams<{ id: string }>();
  const [solicitacao, setSolicitacao] = useState<Solicitacao | null>(null);
  const [historico, setHistorico] = useState<Historico[]>([]);
  const [responsaveis, setResponsaveis] = useState<ResponsavelSuporte[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  
  // Modal states
  const [showEntregaModal, setShowEntregaModal] = useState(false);
  const [showTesteModal, setShowTesteModal] = useState(false);
  const [showAtendimentoModal, setShowAtendimentoModal] = useState(false);
  const [showObservacaoModal, setShowObservacaoModal] = useState(false);
  const [showActionModal, setShowActionModal] = useState(false);
  const [showResponsavelModal, setShowResponsavelModal] = useState(false);
  const [actionType, setActionType] = useState<'reabrir' | 'cancelar' | 'encerrar' | 'enviar' | null>(null);
  const [descricao, setDescricao] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [modalError, setModalError] = useState('');

  const { user, logout } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    if (id) {
      loadData(parseInt(id, 10));
    }
  }, [id]);

  const loadData = async (solicitacaoId: number) => {
    setLoading(true);
    setError('');

    const [solicitacaoResult, historicoResult, responsaveisResult] = await Promise.all([
      solicitacaoService.get(solicitacaoId),
      solicitacaoService.getHistorico(solicitacaoId),
      responsaveisService.listarAtivos(),
    ]);

    setLoading(false);

    if (solicitacaoResult.error) {
      setError(solicitacaoResult.error);
      return;
    }

    if (solicitacaoResult.data) {
      setSolicitacao(solicitacaoResult.data);
    }

    if (historicoResult.data) {
      setHistorico(historicoResult.data);
    }

    if (responsaveisResult.data) {
      setResponsaveis(responsaveisResult.data);
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
    });
  };

  const formatDateTime = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
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
      case 'FUNCIONOU':
      case 'FUNCIONOU_COM_RESSALVA': return 'status-sucesso';
      case 'NAO_FUNCIONOU': return 'status-erro';
      default: return 'status-em-andamento';
    }
  };

  const getEventoIcon = (evento: string): string => {
    switch (evento) {
      case 'SOLICITACAO_CRIADA': return '📝';
      case 'STATUS_ALTERADO': return '🔄';
      case 'SOLICITACAO_EDITADA': return '✏️';
      case 'ENVIADA_SUPORTE': return '📤';
      case 'ATENDIMENTO_INICIADO': return '🎧';
      case 'ATUALIZACAO_ENTREGUE': return '📦';
      case 'TESTE_REALIZADO': return '🧪';
      case 'OBSERVACAO_ADICIONADA': return '💬';
      case 'SOLICITACAO_REABERTA': return '↩️';
      case 'SOLICITACAO_CANCELADA': return '❌';
      case 'SOLICITACAO_ENCERRADA': return '🔒';
      case 'RESPONSAVEL_ATRIBUIDO': return '👤';
      default: return '📋';
    }
  };

  const handleAction = async () => {
    if (!id || !actionType) return;

    setIsSubmitting(true);
    setModalError('');

    let result;
    switch (actionType) {
      case 'reabrir':
        result = await solicitacaoService.reabrir(parseInt(id, 10), descricao);
        break;
      case 'cancelar':
        result = await solicitacaoService.cancelar(parseInt(id, 10), descricao);
        break;
      case 'encerrar':
        result = await solicitacaoService.encerrar(parseInt(id, 10), descricao);
        break;
      case 'enviar':
        result = await solicitacaoService.enviarSuporte(parseInt(id, 10), descricao);
        break;
    }

    setIsSubmitting(false);

    if (result.error) {
      setModalError(result.error);
    } else if (result.data) {
      setSolicitacao(result.data);
      setHistorico([...historico, {
        id: Date.now(),
        solicitacao_id: parseInt(id, 10),
        usuario_id: user?.id ?? 0,
        responsavel_suporte_id: null,
        responsavel_suporte: null,
        evento: actionType === 'reabrir' ? 'SOLICITACAO_REABERTA' : actionType === 'cancelar' ? 'SOLICITACAO_CANCELADA' : actionType === 'encerrar' ? 'SOLICITACAO_ENCERRADA' : 'ENVIADA_SUPORTE',
        evento_label: actionType === 'reabrir' ? 'Solicitação reaberta' : actionType === 'cancelar' ? 'Solicitação cancelada' : actionType === 'encerrar' ? 'Solicitação encerrada' : 'Enviada ao suporte',
        status_anterior: solicitacao?.status ?? null,
        status_anterior_label: solicitacao?.status_label ?? null,
        status_novo: result.data.status,
        status_novo_label: result.data.status_label,
        descricao: descricao || null,
        observacao: null,
        versao_erp: null,
        resultado_teste: null,
        resultado_teste_label: null,
        data_hora_evento: new Date().toISOString(),
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
        usuario: user ? { id: user.id, username: user.username, nome: user.nome } : undefined,
      }]);
      setShowActionModal(false);
      setDescricao('');
      setActionType(null);
    }
  };

  if (loading) {
    return (
      <div className="visualizar-container">
        <div className="loading-state">Carregando...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="visualizar-container">
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
      <div className="visualizar-container">
        <div className="empty-state">Solicitação não encontrada</div>
      </div>
    );
  }

  return (
    <div className="visualizar-container">
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
        <div className="content-grid">
          <div className="main-section">
            <div className="solicitacao-card">
              <div className="card-header">
                <h2>{solicitacao.titulo}</h2>
                <div className="card-actions">
                  <Link to={`/solicitacoes/${id}/editar`} className="btn-edit">
                    ✏️ Editar
                  </Link>
                </div>
              </div>

              <div className="badges">
                <span className={`badge ${getPrioridadeClass(solicitacao.prioridade)}`}>
                  {solicitacao.prioridade_label}
                </span>
                <span className={`badge-status ${getStatusClass(solicitacao.status)}`}>
                  {solicitacao.status_label}
                </span>
                <span className="badge-tipo">{solicitacao.tipo_label}</span>
              </div>

              <div className="info-grid">
                <div className="info-item">
                  <label>Número</label>
                  <span className="numero">{solicitacao.numero}</span>
                </div>
                <div className="info-item">
                  <label>Versão do ERP</label>
                  <span>{solicitacao.versao_erp}</span>
                </div>
                <div className="info-item">
                  <label>Solicitante</label>
                  <span>{solicitacao.solicitante?.nome ?? '-'}</span>
                </div>
                <div className="info-item">
                  <label>Responsável Suporte</label>
                  <div className="responsavel-display">
                    <span>{solicitacao.responsavel_suporte?.nome ?? '-'}</span>
                    <button
                      className="btn-assign-responsavel"
                      onClick={() => setShowResponsavelModal(true)}
                      disabled={['CANCELADA', 'ENCERRADA'].includes(solicitacao.status)}
                    >
                      {solicitacao.responsavel_suporte ? 'Alterar' : 'Atribuir'}
                    </button>
                  </div>
                </div>
                <div className="info-item">
                  <label>Criado em</label>
                  <span>{formatDate(solicitacao.created_at)}</span>
                </div>
              </div>

              <div className="field-group">
                <label>Descrição</label>
                <div className="field-textarea">{solicitacao.descricao}</div>
              </div>

              {solicitacao.observacoes && (
                <div className="field-group">
                  <label>Observações</label>
                  <div className="field-textarea">{solicitacao.observacoes}</div>
                </div>
              )}
            </div>

            {/* Timeline Section */}
            <div className="timeline-card">
              <h3>Histórico / Timeline</h3>
              
              <div className="timeline-actions">
                <button 
                  className="btn-action-timeline"
                  onClick={() => { setActionType('enviar'); setShowActionModal(true); }}
                  disabled={['CANCELADA', 'ENCERRADA'].includes(solicitacao.status)}
                >
                  📤 Enviar ao Suporte
                </button>
                <button 
                  className="btn-action-timeline"
                  onClick={() => setShowAtendimentoModal(true)}
                  disabled={['CANCELADA', 'ENCERRADA'].includes(solicitacao.status)}
                >
                  🎧 Iniciar Atendimento
                </button>
                <button 
                  className="btn-action-timeline"
                  onClick={() => setShowEntregaModal(true)}
                  disabled={['CANCELADA', 'ENCERRADA'].includes(solicitacao.status)}
                >
                  📦 Registrar Entrega
                </button>
                <button 
                  className="btn-action-timeline"
                  onClick={() => setShowTesteModal(true)}
                  disabled={!['ENTREGUE', 'EM_TESTE'].includes(solicitacao.status)}
                >
                  🧪 Registrar Teste
                </button>
                <button 
                  className="btn-action-timeline"
                  onClick={() => { setActionType('reabrir'); setShowActionModal(true); }}
                  disabled={['ABERTA', 'CANCELADA', 'ENCERRADA'].includes(solicitacao.status)}
                >
                  ↩️ Reabrir
                </button>
                <button 
                  className="btn-action-timeline"
                  onClick={() => setShowObservacaoModal(true)}
                >
                  💬 Observação
                </button>
                <button 
                  className="btn-action-timeline btn-danger"
                  onClick={() => { setActionType('cancelar'); setShowActionModal(true); }}
                  disabled={['CANCELADA', 'ENCERRADA'].includes(solicitacao.status)}
                >
                  ❌ Cancelar
                </button>
                <button 
                  className="btn-action-timeline btn-success"
                  onClick={() => { setActionType('encerrar'); setShowActionModal(true); }}
                  disabled={['CANCELADA', 'ENCERRADA'].includes(solicitacao.status)}
                >
                  🔒 Encerrar
                </button>
              </div>

              {historico.length === 0 ? (
                <div className="timeline-empty">
                  <p>Nenhum registro no histórico.</p>
                </div>
              ) : (
                <div className="timeline">
                  {historico.map((item) => (
                    <div key={item.id} className="timeline-item">
                      <div className="timeline-icon">
                        {getEventoIcon(item.evento)}
                      </div>
                      <div className="timeline-content">
                        <div className="timeline-header">
                          <span className="timeline-date">{formatDateTime(item.data_hora_evento)}</span>
                          <span className="timeline-event">{item.evento_label}</span>
                        </div>
                        {item.descricao && (
                          <p className="timeline-descricao">{item.descricao}</p>
                        )}
                        {item.observacao && (
                          <p className="timeline-observacao">{item.observacao}</p>
                        )}
                        {item.responsavel_suporte && (
                          <p className="timeline-info"><strong>Responsável:</strong> {typeof item.responsavel_suporte === 'string' ? item.responsavel_suporte : (item.responsavel_suporte as any).nome}</p>
                        )}
                        {item.versao_erp && (
                          <p className="timeline-info"><strong>Versão:</strong> {item.versao_erp}</p>
                        )}
                        {item.resultado_teste && (
                          <p className="timeline-info">
                            <strong>Resultado:</strong> {item.resultado_teste_label}
                          </p>
                        )}
                        {item.status_anterior && item.status_novo && (
                          <p className="timeline-status">
                            <span className="status-old">{item.status_anterior_label}</span>
                            {' → '}
                            <span className="status-new">{item.status_novo_label}</span>
                          </p>
                        )}
                        <p className="timeline-user">Por: {item.usuario?.nome ?? '-'}</p>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>
      </main>

      <footer className="footer">
        <p>Desenvolvido por Alex Fabiano Longo</p>
      </footer>

      {/* Action Confirmation Modal */}
      {showActionModal && (
        <div className="modal-overlay" onClick={() => setShowActionModal(false)}>
          <div className="modal" onClick={(e) => e.stopPropagation()}>
            <h3>
              {actionType === 'reabrir' && 'Reabrir Solicitação'}
              {actionType === 'cancelar' && 'Cancelar Solicitação'}
              {actionType === 'encerrar' && 'Encerrar Solicitação'}
              {actionType === 'enviar' && 'Enviar ao Suporte'}
            </h3>
            {modalError && <div className="error-message">{modalError}</div>}
            <div className="form-group">
              <label>Descrição (opcional)</label>
              <textarea
                value={descricao}
                onChange={(e) => setDescricao(e.target.value)}
                placeholder="Informe um motivo ou descrição..."
                rows={3}
              />
            </div>
            <div className="modal-actions">
              <button 
                className="btn-confirm"
                onClick={handleAction}
                disabled={isSubmitting}
              >
                {isSubmitting ? 'Aguarde...' : 'Confirmar'}
              </button>
              <button 
                className="btn-cancel"
                onClick={() => { setShowActionModal(false); setDescricao(''); setActionType(null); }}
              >
                Cancelar
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Observacao Modal */}
      {showObservacaoModal && (
        <ObservacaoModal
          solicitacaoId={parseInt(id ?? '0', 10)}
          onClose={() => setShowObservacaoModal(false)}
          onSuccess={(novoHistorico) => {
            setHistorico([...historico, novoHistorico]);
            setShowObservacaoModal(false);
          }}
          user={user}
        />
      )}

      {/* Atendimento Modal */}
      {showAtendimentoModal && (
        <AtendimentoModal
          solicitacaoId={parseInt(id ?? '0', 10)}
          responsaveis={responsaveis}
          onClose={() => setShowAtendimentoModal(false)}
          onSuccess={(novoHistorico, novoStatus) => {
            setHistorico([...historico, novoHistorico]);
            if (novoStatus) setSolicitacao({ ...solicitacao, status: novoStatus.status, status_label: novoStatus.status_label });
            setShowAtendimentoModal(false);
          }}
          user={user}
        />
      )}

      {/* Entrega Modal */}
      {showEntregaModal && (
        <EntregaModal
          solicitacaoId={parseInt(id ?? '0', 10)}
          responsaveis={responsaveis}
          onClose={() => setShowEntregaModal(false)}
          onSuccess={(novoHistorico, novoStatus) => {
            setHistorico([...historico, novoHistorico]);
            if (novoStatus) setSolicitacao({ ...solicitacao, status: novoStatus.status, status_label: novoStatus.status_label });
            setShowEntregaModal(false);
          }}
          user={user}
        />
      )}

      {/* Teste Modal */}
      {showTesteModal && (
        <TesteModal
          solicitacaoId={parseInt(id ?? '0', 10)}
          onClose={() => setShowTesteModal(false)}
          onSuccess={(novoHistorico, novoStatus) => {
            setHistorico([...historico, novoHistorico]);
            if (novoStatus) setSolicitacao({ ...solicitacao, status: novoStatus.status, status_label: novoStatus.status_label });
            setShowTesteModal(false);
          }}
          user={user}
        />
      )}

      {/* Responsavel Modal */}
      {showResponsavelModal && (
        <ResponsavelModal
          solicitacaoId={parseInt(id ?? '0', 10)}
          responsaveis={responsaveis}
          responsavelAtual={solicitacao.responsavel_suporte}
          onClose={() => setShowResponsavelModal(false)}
          onSuccess={(novoHistorico, novaSolicitacao) => {
            setHistorico([...historico, novoHistorico]);
            if (novaSolicitacao) setSolicitacao(novaSolicitacao);
            setShowResponsavelModal(false);
          }}
          user={user}
        />
      )}
    </div>
  );
}

// Observacao Modal Component
function ObservacaoModal({ solicitacaoId, onClose, onSuccess, user }: {
  solicitacaoId: number;
  onClose: () => void;
  onSuccess: (historico: Historico) => void;
  user: any;
}) {
  const [observacao, setObservacao] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async () => {
    if (!observacao.trim()) return;

    setIsSubmitting(true);
    setError('');

    const result = await solicitacaoService.adicionarObservacao(solicitacaoId, observacao);

    setIsSubmitting(false);

    if (result.error) {
      setError(result.error);
    } else {
      onSuccess({
        id: Date.now(),
        solicitacao_id: solicitacaoId,
        usuario_id: user?.id ?? 0,
        responsavel_suporte_id: null,
        responsavel_suporte: null,
        evento: 'OBSERVACAO_ADICIONADA',
        evento_label: 'Observação adicionada',
        status_anterior: null,
        status_anterior_label: null,
        status_novo: null,
        status_novo_label: null,
        descricao: null,
        observacao: observacao,
        versao_erp: null,
        resultado_teste: null,
        resultado_teste_label: null,
        data_hora_evento: new Date().toISOString(),
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
        usuario: user ? { id: user.id, username: user.username, nome: user.nome } : undefined,
      });
    }
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal" onClick={(e) => e.stopPropagation()}>
        <h3>Adicionar Observação</h3>
        {error && <div className="error-message">{error}</div>}
        <div className="form-group">
          <label>Observação</label>
          <textarea
            value={observacao}
            onChange={(e) => setObservacao(e.target.value)}
            placeholder="Digite sua observação..."
            rows={4}
            autoFocus
          />
        </div>
        <div className="modal-actions">
          <button className="btn-confirm" onClick={handleSubmit} disabled={isSubmitting || !observacao.trim()}>
            {isSubmitting ? 'Aguarde...' : 'Adicionar'}
          </button>
          <button className="btn-cancel" onClick={onClose}>Cancelar</button>
        </div>
      </div>
    </div>
  );
}

// Atendimento Modal Component
function AtendimentoModal({ solicitacaoId, responsaveis, onClose, onSuccess, user }: {
  solicitacaoId: number;
  responsaveis: ResponsavelSuporte[];
  onClose: () => void;
  onSuccess: (historico: Historico, status?: { status: string; status_label: string }) => void;
  user: any;
}) {
  const [responsavelId, setResponsavelId] = useState<string>('');
  const [descricao, setDescricao] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async () => {
    if (!responsavelId) return;

    setIsSubmitting(true);
    setError('');

    const selectedResponsavel = responsaveis.find(r => r.id === parseInt(responsavelId, 10));

    const result = await solicitacaoService.registrarAtendimento(solicitacaoId, {
      responsavel_suporte_id: parseInt(responsavelId, 10),
      descricao: descricao || undefined,
    });

    setIsSubmitting(false);

    if (result.error) {
      setError(result.error);
    } else if (result.data) {
      onSuccess({
        id: Date.now(),
        solicitacao_id: solicitacaoId,
        usuario_id: user?.id ?? 0,
        responsavel_suporte_id: parseInt(responsavelId, 10),
        responsavel_suporte: selectedResponsavel ?? responsavelId,
        evento: 'ATENDIMENTO_INICIADO',
        evento_label: 'Atendimento iniciado',
        status_anterior: 'ENVIADA_AO_SUPORTE',
        status_anterior_label: 'Enviada ao suporte',
        status_novo: 'EM_ANALISE',
        status_novo_label: 'Em análise',
        descricao: descricao || null,
        observacao: null,
        versao_erp: null,
        resultado_teste: null,
        resultado_teste_label: null,
        data_hora_evento: new Date().toISOString(),
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
        usuario: user ? { id: user.id, username: user.username, nome: user.nome } : undefined,
      }, { status: result.data.status, status_label: result.data.status_label });
    }
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal" onClick={(e) => e.stopPropagation()}>
        <h3>Iniciar Atendimento</h3>
        {error && <div className="error-message">{error}</div>}
        <div className="form-group">
          <label>Responsável do Suporte *</label>
          <select
            value={responsavelId}
            onChange={(e) => setResponsavelId(e.target.value)}
            autoFocus
          >
            <option value="">Selecione um responsável...</option>
            {responsaveis.map((r) => (
              <option key={r.id} value={r.id}>{r.nome} {r.empresa ? `(${r.empresa})` : ''}</option>
            ))}
          </select>
        </div>
        <div className="form-group">
          <label>Descrição (opcional)</label>
          <textarea
            value={descricao}
            onChange={(e) => setDescricao(e.target.value)}
            placeholder="Informações adicionais..."
            rows={3}
          />
        </div>
        <div className="modal-actions">
          <button className="btn-confirm" onClick={handleSubmit} disabled={isSubmitting || !responsavelId}>
            {isSubmitting ? 'Aguarde...' : 'Confirmar'}
          </button>
          <button className="btn-cancel" onClick={onClose}>Cancelar</button>
        </div>
      </div>
    </div>
  );
}

// Entrega Modal Component
function EntregaModal({ solicitacaoId, responsaveis, onClose, onSuccess, user }: {
  solicitacaoId: number;
  responsaveis: ResponsavelSuporte[];
  onClose: () => void;
  onSuccess: (historico: Historico, status?: { status: string; status_label: string }) => void;
  user: any;
}) {
  const [versao, setVersao] = useState('');
  const [descricao, setDescricao] = useState('');
  const [responsavelId, setResponsavelId] = useState<string>('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async () => {
    if (!versao.trim()) return;

    setIsSubmitting(true);
    setError('');

    const selectedResponsavel = responsavelId ? responsaveis.find(r => r.id === parseInt(responsavelId, 10)) : null;

    const result = await solicitacaoService.registrarEntrega(solicitacaoId, {
      versao_entregue: versao,
      descricao: descricao || undefined,
      responsavel_suporte: selectedResponsavel?.nome || undefined,
    });

    setIsSubmitting(false);

    if (result.error) {
      setError(result.error);
    } else if (result.data) {
      onSuccess({
        id: Date.now(),
        solicitacao_id: solicitacaoId,
        usuario_id: user?.id ?? 0,
        responsavel_suporte_id: responsavelId ? parseInt(responsavelId, 10) : null,
        responsavel_suporte: selectedResponsavel ?? (responsavelId || null),
        evento: 'ATUALIZACAO_ENTREGUE',
        evento_label: 'Atualização entregue',
        status_anterior: result.data.status,
        status_anterior_label: result.data.status_label,
        status_novo: 'ENTREGUE',
        status_novo_label: 'Entregue',
        descricao: descricao || null,
        observacao: null,
        versao_erp: versao,
        resultado_teste: null,
        resultado_teste_label: null,
        data_hora_evento: new Date().toISOString(),
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
        usuario: user ? { id: user.id, username: user.username, nome: user.nome } : undefined,
      }, { status: result.data.status, status_label: result.data.status_label });
    }
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal" onClick={(e) => e.stopPropagation()}>
        <h3>Registrar Entrega</h3>
        {error && <div className="error-message">{error}</div>}
        <div className="form-group">
          <label>Versão Entregue *</label>
          <input
            type="text"
            value={versao}
            onChange={(e) => setVersao(e.target.value)}
            placeholder="Ex: 2026.09.07, Build 07-09-2026"
          />
          <small>Texto livre - versão em que a solução foi entregue</small>
        </div>
        <div className="form-group">
          <label>Responsável (opcional)</label>
          <select
            value={responsavelId}
            onChange={(e) => setResponsavelId(e.target.value)}
          >
            <option value="">Selecione um responsável...</option>
            {responsaveis.map((r) => (
              <option key={r.id} value={r.id}>{r.nome} {r.empresa ? `(${r.empresa})` : ''}</option>
            ))}
          </select>
        </div>
        <div className="form-group">
          <label>Descrição (opcional)</label>
          <textarea
            value={descricao}
            onChange={(e) => setDescricao(e.target.value)}
            placeholder="Informações sobre a entrega..."
            rows={3}
          />
        </div>
        <div className="modal-actions">
          <button className="btn-confirm" onClick={handleSubmit} disabled={isSubmitting || !versao.trim()}>
            {isSubmitting ? 'Aguarde...' : 'Confirmar'}
          </button>
          <button className="btn-cancel" onClick={onClose}>Cancelar</button>
        </div>
      </div>
    </div>
  );
}

// Teste Modal Component
function TesteModal({ solicitacaoId, onClose, onSuccess, user }: {
  solicitacaoId: number;
  onClose: () => void;
  onSuccess: (historico: Historico, status?: { status: string; status_label: string }) => void;
  user: any;
}) {
  const [resultado, setResultado] = useState<'FUNCIONOU' | 'FUNCIONOU_COM_RESSALVA' | 'NAO_FUNCIONOU' | ''>('');
  const [observacao, setObservacao] = useState('');
  const [versaoTestada, setVersaoTestada] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async () => {
    if (!resultado) return;

    setIsSubmitting(true);
    setError('');

    const result = await solicitacaoService.registrarTeste(solicitacaoId, {
      resultado,
      observacao: observacao || undefined,
      versao_testada: versaoTestada || undefined,
    });

    setIsSubmitting(false);

    if (result.error) {
      setError(result.error);
    } else if (result.data) {
      const resultadoLabel = resultado === 'FUNCIONOU' ? 'Funcionou' : resultado === 'FUNCIONOU_COM_RESSALVA' ? 'Funcionou com ressalva' : 'Não funcionou';
      onSuccess({
        id: Date.now(),
        solicitacao_id: solicitacaoId,
        usuario_id: user?.id ?? 0,
        responsavel_suporte_id: null,
        responsavel_suporte: null,
        evento: 'TESTE_REALIZADO',
        evento_label: 'Teste realizado',
        status_anterior: result.data.status,
        status_anterior_label: result.data.status_label,
        status_novo: resultado,
        status_novo_label: resultadoLabel,
        descricao: null,
        observacao: observacao || null,
        versao_erp: versaoTestada || null,
        resultado_teste: resultado,
        resultado_teste_label: resultadoLabel,
        data_hora_evento: new Date().toISOString(),
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
        usuario: user ? { id: user.id, username: user.username, nome: user.nome } : undefined,
      }, { status: result.data.status, status_label: result.data.status_label });
    }
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal" onClick={(e) => e.stopPropagation()}>
        <h3>Registrar Resultado do Teste</h3>
        {error && <div className="error-message">{error}</div>}
        <div className="form-group">
          <label>Resultado *</label>
          <select value={resultado} onChange={(e) => setResultado(e.target.value as any)}>
            <option value="">Selecione...</option>
            <option value="FUNCIONOU">Funcionou</option>
            <option value="FUNCIONOU_COM_RESSALVA">Funcionou com ressalva</option>
            <option value="NAO_FUNCIONOU">Não funcionou</option>
          </select>
        </div>
        <div className="form-group">
          <label>Versão Testada (opcional)</label>
          <input
            type="text"
            value={versaoTestada}
            onChange={(e) => setVersaoTestada(e.target.value)}
            placeholder="Ex: 2026.09.07"
          />
        </div>
        <div className="form-group">
          <label>Observação (opcional)</label>
          <textarea
            value={observacao}
            onChange={(e) => setObservacao(e.target.value)}
            placeholder={resultado === 'FUNCIONOU_COM_RESSALVA' ? 'Descreva a ressalva...' : resultado === 'NAO_FUNCIONOU' ? 'Descreva o problema encontrado...' : 'Informações adicionais...'}
            rows={3}
          />
        </div>
        <div className="modal-actions">
          <button className="btn-confirm" onClick={handleSubmit} disabled={isSubmitting || !resultado}>
            {isSubmitting ? 'Aguarde...' : 'Confirmar'}
          </button>
          <button className="btn-cancel" onClick={onClose}>Cancelar</button>
        </div>
      </div>
    </div>
  );
}

// Responsavel Modal Component
function ResponsavelModal({ solicitacaoId, responsaveis, responsavelAtual, onClose, onSuccess, user }: {
  solicitacaoId: number;
  responsaveis: ResponsavelSuporte[];
  responsavelAtual: ResponsavelSuporte | null | undefined;
  onClose: () => void;
  onSuccess: (historico: Historico, solicitacao?: Solicitacao) => void;
  user: any;
}) {
  const [responsavelId, setResponsavelId] = useState<string>(responsavelAtual?.id?.toString() ?? '');
  const [descricao, setDescricao] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async () => {
    if (!responsavelId) return;

    setIsSubmitting(true);
    setError('');

    const result = await solicitacaoService.atribuirResponsavel(solicitacaoId, parseInt(responsavelId, 10), descricao || undefined);

    setIsSubmitting(false);

    if (result.error) {
      setError(result.error);
    } else if (result.data) {
      const selectedResponsavel = responsaveis.find(r => r.id === parseInt(responsavelId, 10));
      onSuccess({
        id: Date.now(),
        solicitacao_id: solicitacaoId,
        usuario_id: user?.id ?? 0,
        responsavel_suporte_id: parseInt(responsavelId, 10),
        responsavel_suporte: selectedResponsavel ?? responsavelId,
        evento: 'RESPONSAVEL_ATRIBUIDO',
        evento_label: 'Responsável atribuído',
        status_anterior: null,
        status_anterior_label: null,
        status_novo: null,
        status_novo_label: null,
        descricao: descricao || null,
        observacao: null,
        versao_erp: null,
        resultado_teste: null,
        resultado_teste_label: null,
        data_hora_evento: new Date().toISOString(),
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
        usuario: user ? { id: user.id, username: user.username, nome: user.nome } : undefined,
      }, result.data);
    }
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal" onClick={(e) => e.stopPropagation()}>
        <h3>Atribuir Responsável do Suporte</h3>
        {error && <div className="error-message">{error}</div>}
        <div className="form-group">
          <label>Responsável</label>
          <select
            value={responsavelId}
            onChange={(e) => setResponsavelId(e.target.value)}
            autoFocus
          >
            <option value="">Selecione um responsável...</option>
            {responsaveis.map((r) => (
              <option key={r.id} value={r.id}>{r.nome} {r.empresa ? `(${r.empresa})` : ''}</option>
            ))}
          </select>
        </div>
        <div className="form-group">
          <label>Descrição (opcional)</label>
          <textarea
            value={descricao}
            onChange={(e) => setDescricao(e.target.value)}
            placeholder="Motivo da alteração..."
            rows={3}
          />
        </div>
        <div className="modal-actions">
          <button className="btn-confirm" onClick={handleSubmit} disabled={isSubmitting || !responsavelId}>
            {isSubmitting ? 'Aguarde...' : 'Confirmar'}
          </button>
          <button className="btn-cancel" onClick={onClose}>Cancelar</button>
        </div>
      </div>
    </div>
  );
}

export default VisualizarSolicitacaoPage;
