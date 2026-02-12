export interface ReservaMatrimonio {
    id: number;
    title: string;
    start: Date;
    end: Date;
    description?: string;
    
    // Campos específicos
    ent_id?: number; // ID da comunidade (opcional)
    local?: string; // Nome do local (se não for comunidade, ou fallback)
    local_nome?: string; // Nome para exibição
    telefone_noivo?: string;
    telefone_noiva?: string;
    efeito_civil: boolean;
    color?: string;
    paroquia_id?: number;
    
    extendedProps?: any;
    allDay?: boolean;
    backgroundColor?: string;
    borderColor?: string;
}

export interface Local {
    id: number;
    nome: string;
}

export interface RegraMatrimonio {
    comunidade_id: number;
    nome: string;
    max_casamentos_por_dia: number;
    dias_permitidos: string[]; // "0", "1", etc.
}
