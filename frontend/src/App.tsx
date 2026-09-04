import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';
import { ProtectedRoute } from './components/ProtectedRoute';
import { LoginPage } from './pages/LoginPage';
import { HomePage } from './pages/HomePage';
import { ListaSolicitacoesPage } from './pages/ListaSolicitacoesPage';
import { NovaSolicitacaoPage } from './pages/NovaSolicitacaoPage';
import { VisualizarSolicitacaoPage } from './pages/VisualizarSolicitacaoPage';
import { EditarSolicitacaoPage } from './pages/EditarSolicitacaoPage';

/**
 * Componente principal da aplicação
 */
function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Routes>
          <Route path="/login" element={<LoginPage />} />
          <Route
            path="/home"
            element={
              <ProtectedRoute>
                <HomePage />
              </ProtectedRoute>
            }
          />
          <Route
            path="/solicitacoes"
            element={
              <ProtectedRoute>
                <ListaSolicitacoesPage />
              </ProtectedRoute>
            }
          />
          <Route
            path="/solicitacoes/nova"
            element={
              <ProtectedRoute>
                <NovaSolicitacaoPage />
              </ProtectedRoute>
            }
          />
          <Route
            path="/solicitacoes/:id"
            element={
              <ProtectedRoute>
                <VisualizarSolicitacaoPage />
              </ProtectedRoute>
            }
          />
          <Route
            path="/solicitacoes/:id/editar"
            element={
              <ProtectedRoute>
                <EditarSolicitacaoPage />
              </ProtectedRoute>
            }
          />
          <Route path="/" element={<Navigate to="/home" replace />} />
          <Route path="*" element={<Navigate to="/home" replace />} />
        </Routes>
      </BrowserRouter>
    </AuthProvider>
  );
}

export default App;
