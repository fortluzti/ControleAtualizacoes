/**
 * Serviço de Solicitações
 */

import api from './api';

export interface Solicitacao {
  id: number;
  numero: string;
  titulo: string;
  descricao: string;
  tipo: 'CORRECAO' | 'ALTERACAO' | 'MELHORIA' | 'DUVIDA';
  tipo_label: string;
  prioridade: 'BAIXA' | 'NORMAL' | 'ALTA' | 'URGENTE';
  prioridade_label: string;
  status: string;
  status_label: string;
  solicitante_id: number;
  versao_erp: string;
  observacoes: string | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  solicitante?: {
    id: number;
    username: string;
    nome: string;
  };
}

export interface SolicitacaoCreate {
  titulo: string;
  descricao: string;
  tipo: string;
  prioridade: string;
  versao_erp: string;
  observacoes?: string;
}

export interface SolicitacaoUpdate {
  titulo?: string;
  descricao?: string;
  tipo?: string;
  prioridade?: string;
  status?: string;
  versao_erp?: string;
  observacoes?: string;
}

export interface PaginationInfo {
  total: number;
  page: number;
  per_page: number;
  total_pages: number;
}

export interface ListResponse {
  data: Solicitacao[];
  pagination: PaginationInfo;
}

export interface OpcoesResponse {
  tipos: Record<string, string>;
  prioridades: Record<string, string>;
  status: Record<string, string>;
}

interface ApiError {
  error?: string;
  errors?: string[];
}

export const solicitacaoService = {
  /**
   * Lista solicitações com filtros opcionais.
   */
  async list(params?: {
    numero?: string;
    titulo?: string;
    status?: string;
    prioridade?: string;
    page?: number;
    per_page?: number;
  }): Promise<{ data?: ListResponse; error?: string }> {
    const searchParams = new URLSearchParams();
    
    if (params?.numero) searchParams.set('numero', params.numero);
    if (params?.titulo) searchParams.set('titulo', params.titulo);
    if (params?.status) searchParams.set('status', params.status);
    if (params?.prioridade) searchParams.set('prioridade', params.prioridade);
    if (params?.page) searchParams.set('page', String(params.page));
    if (params?.per_page) searchParams.set('per_page', String(params.per_page));

    const queryString = searchParams.toString();
    const url = `/solicitacoes${queryString ? '?' + queryString : ''}`;

    const response = await api.get<ListResponse>(url);

    if (response.error) {
      return { error: response.error };
    }

    return { data: response.data };
  },

  /**
   * Busca uma solicitação pelo ID.
   */
  async get(id: number): Promise<{ data?: Solicitacao; error?: string }> {
    const response = await api.get<{ data: Solicitacao }>(`/solicitacoes/${id}`);

    if (response.error) {
      return { error: response.error };
    }

    return { data: response.data?.data };
  },

  /**
   * Cria uma nova solicitação.
   */
  async create(data: SolicitacaoCreate): Promise<{ data?: Solicitacao; errors?: string[]; error?: string }> {
    const response = await api.post<{ data: Solicitacao; message: string } | ApiError>('/solicitacoes', data);

    if (response.error) {
      return { error: response.error };
    }

    if ('errors' in (response.data ?? {})) {
      return { errors: (response.data as ApiError).errors };
    }

    return { data: (response.data as { data: Solicitacao }).data };
  },

  /**
   * Atualiza uma solicitação.
   */
  async update(id: number, data: SolicitacaoUpdate): Promise<{ data?: Solicitacao; errors?: string[]; error?: string }> {
    const response = await api.put<{ data: Solicitacao; message: string } | ApiError>(`/solicitacoes/${id}`, data);

    if (response.error) {
      return { error: response.error };
    }

    if ('errors' in (response.data ?? {})) {
      return { errors: (response.data as ApiError).errors };
    }

    return { data: (response.data as { data: Solicitacao }).data };
  },

  /**
   * Exclui uma solicitação (soft delete).
   */
  async delete(id: number): Promise<{ error?: string }> {
    const response = await api.delete<{ message: string }>(`/solicitacoes/${id}`);

    if (response.error) {
      return { error: response.error };
    }

    return {};
  },

  /**
   * Busca as opções para selects.
   */
  async opcoes(): Promise<{ data?: OpcoesResponse; error?: string }> {
    const response = await api.get<OpcoesResponse>('/solicitacoes/opcoes');

    if (response.error) {
      return { error: response.error };
    }

    return { data: response.data };
  },
};

export default solicitacaoService;
