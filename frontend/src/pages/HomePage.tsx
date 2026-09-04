/**
 * Página Inicial Autenticada
 */

import { useAuth } from '../contexts/AuthContext';
import { Link, useNavigate } from 'react-router-dom';
import './HomePage.css';

export function HomePage() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <div className="home-container">
      <header className="home-header">
        <div className="header-content">
          <h1>ControleAtualizacoes</h1>
          <button onClick={handleLogout} className="logout-button">
            Sair
          </button>
        </div>
      </header>

      <nav className="home-nav">
        <Link to="/home" className="nav-link active">Início</Link>
        <Link to="/solicitacoes" className="nav-link">Solicitações</Link>
        <Link to="/solicitacoes/nova" className="nav-link">Nova Solicitação</Link>
      </nav>

      <main className="home-main">
        <div className="welcome-card">
          <h2>Bem-vindo, {user?.nome}!</h2>
          
          <div className="user-info">
            <div className="info-row">
              <span className="info-label">Usuário:</span>
              <span className="info-value">{user?.username}</span>
            </div>
            <div className="info-row">
              <span className="info-label">Nome:</span>
              <span className="info-value">{user?.nome}</span>
            </div>
            <div className="info-row">
              <span className="info-label">Perfil:</span>
              <span className="info-value perfil">{user?.perfil}</span>
            </div>
          </div>

          <div className="quick-actions">
            <h3>Ações Rápidas</h3>
            <div className="action-buttons">
              <Link to="/solicitacoes" className="action-button">
                📋 Ver Solicitações
              </Link>
              <Link to="/solicitacoes/nova" className="action-button primary">
                ➕ Nova Solicitação
              </Link>
            </div>
          </div>

          <div className="status-section">
            <h3>Status do Sistema</h3>
            <div className="status-grid">
              <div className="status-item">
                <span className="status-indicator online"></span>
                <span>Frontend Online</span>
              </div>
              <div className="status-item">
                <span className="status-indicator online"></span>
                <span>Autenticado</span>
              </div>
            </div>
          </div>
        </div>
      </main>

      <footer className="home-footer">
        <p>Desenvolvido por Alex Fabiano Longo</p>
      </footer>
    </div>
  );
}

export default HomePage;
