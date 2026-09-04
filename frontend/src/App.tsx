import { useState, useEffect } from 'react'
import './App.css'

interface ApiHealth {
  status: string;
  message?: string;
}

function App() {
  const [apiStatus, setApiStatus] = useState<'loading' | 'online' | 'offline'>('loading');
  const [apiData, setApiData] = useState<ApiHealth | null>(null);

  useEffect(() => {
    const checkApiHealth = async () => {
      try {
        const response = await fetch(`${import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'}/health`);
        if (response.ok) {
          const data = await response.json();
          setApiData(data);
          setApiStatus('online');
        } else {
          setApiStatus('offline');
        }
      } catch {
        setApiStatus('offline');
      }
    };

    checkApiHealth();
  }, []);

  return (
    <div className="container">
      <header className="header">
        <h1>ControleAtualizacoes</h1>
        <p className="subtitle">Sistema de Controle de Solicitações</p>
      </header>

      <main className="main">
        <section className="status-section">
          <h2>Status do Sistema</h2>
          
          <div className="status-item">
            <span className="status-label">Frontend:</span>
            <span className="status-indicator online">Online</span>
          </div>

          <div className="status-item">
            <span className="status-label">API:</span>
            {apiStatus === 'loading' && (
              <span className="status-indicator loading">Verificando...</span>
            )}
            {apiStatus === 'online' && (
              <span className="status-indicator online">
                Online
                {apiData && <span className="api-response"> - {JSON.stringify(apiData)}</span>}
              </span>
            )}
            {apiStatus === 'offline' && (
              <span className="status-indicator offline">Offline</span>
            )}
          </div>
        </section>

        <section className="info-section">
          <h2>Informações</h2>
          <p className="version">Versão: 0.1.0 (Fase 1)</p>
        </section>
      </main>

      <footer className="footer">
        <p>Desenvolvido por Alex Fabiano Longo</p>
      </footer>
    </div>
  );
}

export default App
