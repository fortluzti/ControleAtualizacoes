/**
 * Serviço de Autenticação
 */

import api from './api';

export interface User {
  id: number;
  username: string;
  nome: string;
  perfil: 'ADMIN' | 'USUARIO';
  ativo: boolean;
  created_at: string;
  updated_at: string;
}

interface LoginResponse {
  message: string;
  token: string;
  user: User;
}

interface MeResponse {
  user: User;
}

export const authService = {
  async login(username: string, password: string): Promise<{ user?: User; token?: string; error?: string }> {
    const response = await api.post<LoginResponse>('/auth/login', { username, password });

    if (response.error) {
      return { error: response.error };
    }

    if (response.data) {
      return {
        user: response.data.user,
        token: response.data.token,
      };
    }

    return { error: 'Erro desconhecido' };
  },

  async logout(): Promise<{ error?: string }> {
    const response = await api.post<{ message: string }>('/auth/logout');

    if (response.error) {
      return { error: response.error };
    }

    return {};
  },

  async me(): Promise<{ user?: User; error?: string }> {
    const response = await api.get<MeResponse>('/auth/me');

    if (response.error) {
      return { error: response.error };
    }

    if (response.data) {
      return { user: response.data.user };
    }

    return { error: 'Erro desconhecido' };
  },

  setToken(token: string): void {
    localStorage.setItem('auth_token', token);
  },

  getToken(): string | null {
    return localStorage.getItem('auth_token');
  },

  removeToken(): void {
    localStorage.removeItem('auth_token');
  },

  setUser(user: User): void {
    sessionStorage.setItem('auth_user', JSON.stringify(user));
  },

  getUser(): User | null {
    const userStr = sessionStorage.getItem('auth_user');
    if (userStr) {
      try {
        return JSON.parse(userStr) as User;
      } catch {
        return null;
      }
    }
    return null;
  },

  removeUser(): void {
    sessionStorage.removeItem('auth_user');
  },

  clearAuth(): void {
    this.removeToken();
    this.removeUser();
  },
};

export default authService;
