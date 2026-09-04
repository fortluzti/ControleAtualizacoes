/**
 * Página Inicial Autenticada
 */

import { useAuth } from '../contexts/AuthContext';
import './HomePage.css';

export function HomePage() {
  const { user, logout } = useAuth();

  const handleLogout = async () => {
    await logout();
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
