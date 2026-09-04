/**
 * Serviço de Responsáveis do Suporte
 */

import api from './api';
import type { ResponsavelSuporte } from './solicitacao';

export interface ResponsavelSuporteCreate {
  nome: string;
  telefone?: string;
  email?: string;
  empresa?: string;
  observacoes?: string;
  ativo?: boolean;
}

const responsaveisService = {
  /**
   * Lista todos os responsáveis.
   */
  async listar(): Promise<{ data?: ResponsavelSuporte[]; error?: string }> {
    const response = await api.get<{ data: ResponsavelSuporte[] }>('/responsaveis-suporte');

    if (response.error) {
      return { error: response.error };
    }

    return { data: response.data?.data };
  },

  /**
   * Lista responsáveis ativos.
   */
  async listarAtivos(): Promise<{ data?: ResponsavelSuporte[]; error?: string }> {
    const response = await api.get<{ data: ResponsavelSuporte[] }>('/responsaveis-suporte/ativos');

    if (response.error) {
      return { error: response.error };
    }

    return { data: response.data?.data };
  },

  /**
   * Busca um responsável pelo ID.
   */
  async buscar(id: number): Promise<{ data?: ResponsavelSuporte; error?: string }> {
    const response = await api.get<{ data: ResponsavelSuporte }>(`/responsaveis-suporte/${id}`);

    if (response.error) {
      return { error: response.error };
    }

    return { data: response.data?.data };
  },

  /**
   * Cria um novo responsável.
   */
  async criar(data: ResponsavelSuporteCreate): Promise<{ data?: ResponsavelSuporte; error?: string }> {
    const response = await api.post<{ data: ResponsavelSuporte; message: string }>('/responsaveis-suporte', data);

    if (response.error) {
      return { error: response.error };
    }

    return { data: (response.data as { data: ResponsavelSuporte })?.data };
  },

  /**
   * Atualiza um responsável.
   */
  async atualizar(id: number, data: Partial<ResponsavelSuporteCreate>): Promise<{ data?: ResponsavelSuporte; error?: string }> {
    const response = await api.put<{ data: ResponsavelSuporte; message: string }>(`/responsaveis-suporte/${id}`, data);

    if (response.error) {
      return { error: response.error };
    }

    return { data: (response.data as { data: ResponsavelSuporte })?.data };
  },

  /**
   * Ativa um responsável.
   */
  async ativar(id: number): Promise<{ data?: ResponsavelSuporte; error?: string }> {
    const response = await api.post<{ data: ResponsavelSuporte; message: string }>(`/responsaveis-suporte/${id}/ativar`);

    if (response.error) {
      return { error: response.error };
    }

    return { data: (response.data as { data: ResponsavelSuporte })?.data };
  },

  /**
   * Desativa um responsável.
   */
  async desativar(id: number): Promise<{ data?: ResponsavelSuporte; error?: string }> {
    const response = await api.post<{ data: ResponsavelSuporte; message: string }>(`/responsaveis-suporte/${id}/desativar`);

    if (response.error) {
      return { error: response.error };
    }

    return { data: (response.data as { data: ResponsavelSuporte })?.data };
  },
};

export default responsaveisService;
