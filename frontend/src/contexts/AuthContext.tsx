/**
 * Contexto de Autenticação
 * 
 * Gerencia o estado de autenticação da aplicação.
 */

import { createContext, useContext, useState, useEffect } from 'react';
import type { ReactNode } from 'react';
import type { User } from '../services/auth';
import { authService } from '../services/auth';

interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (username: string, password: string) => Promise<{ error?: string }>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  // Verifica autenticação ao iniciar
  useEffect(() => {
    const checkAuth = async () => {
      const token = authService.getToken();
      if (token) {
        const result = await authService.me();
        if (result.user) {
          setUser(result.user);
          authService.setUser(result.user);
        } else {
          authService.clearAuth();
        }
      }
      setIsLoading(false);
    };

    checkAuth();
  }, []);

  const login = async (username: string, password: string): Promise<{ error?: string }> => {
    const result = await authService.login(username, password);

    if (result.error) {
      return { error: result.error };
    }

    if (result.user && result.token) {
      authService.setToken(result.token);
      authService.setUser(result.user);
      setUser(result.user);
      return {};
    }

    return { error: 'Erro desconhecido' };
  };

  const logout = async (): Promise<void> => {
    await authService.logout();
    authService.clearAuth();
    setUser(null);
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        isAuthenticated: !!user,
        isLoading,
        login,
        logout,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthContextType {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}

export default AuthContext;
