export interface Reserva {
    id?: number | string;
    title: string;
    start: Date;
    end: Date;
    resourceId?: number; // For local/room
    description?: string;
    responsavel?: string;
    observacoes?: string;
    color?: string;
    paroquia_id?: number;
    local?: number | Local; // ID of the local or Local object
    allDay?: boolean;
    isHoliday?: boolean;
}

export interface Local {
    id: number;
    name: string;
    foto?: string;
    paroquia_id: number;
}

export interface Holiday {
    date: string;
    title: string;
    type: 'feriado' | 'catolico' | 'optional';
}
