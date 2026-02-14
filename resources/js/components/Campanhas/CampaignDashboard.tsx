import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, CartesianGrid, Cell } from 'recharts';
import { Download, ArrowUpCircle, ArrowDownCircle, Trash2 } from 'lucide-react';
import { format } from 'date-fns';

interface DashboardData {
    campanha: {
        id: number;
        nome: string;
        descricao: string;
        status: string;
    };
    stats: {
        total_arrecadado: number;
        total_gasto: number;
        saldo: number;
    };
    entradas: any[];
    saidas: any[];
    movimentacoes: any[];
}

export default function CampaignDashboard({ campanhaId, paroquiaName, isOpen, onClose }: { campanhaId: number; paroquiaName: string; isOpen: boolean; onClose: () => void }) {
    const [data, setData] = useState<DashboardData | null>(null);
    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState<'entradas' | 'saidas' | 'completo'>('completo');

    // Modal states
    const [showEntradaModal, setShowEntradaModal] = useState(false);
    const [showSaidaModal, setShowSaidaModal] = useState(false);
    const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
    const [deleteTarget, setDeleteTarget] = useState<{ type: 'entrada' | 'saida'; id: number } | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);

    const [formData, setFormData] = useState({
        data: format(new Date(), 'yyyy-MM-dd'),
        valor: '',
        forma: '', // Para entrada
        categoria: '', // Para saida
        descricao: '', // Para saida
        observacoes: '' // Para entrada
    });

    useEffect(() => {
        if (isOpen && campanhaId) {
            fetchData();
        }
    }, [isOpen, campanhaId]);

    const fetchData = async () => {
        setLoading(true);
        try {
            const res = await axios.get(`/api/campanhas/${campanhaId}/dashboard-data`);
            setData(res.data);
        } catch (error) {
            console.error("Error fetching dashboard data", error);
        } finally {
            setLoading(false);
        }
    };

    // Currency mask function for input
    const formatCurrencyInput = (value: string) => {
        const v = value.replace(/\D/g, '');
        const val = (parseInt(v) / 100).toFixed(2).replace('.', ',');
        return v ? `R$ ${val.replace(/\B(?=(\d{3})+(?!\d))/g, '.')}` : '';
    };

    // Convert currency string to number for API
    const parseCurrency = (value: string) => {
        if (!value) return 0;
        return parseFloat(value.replace('R$', '').replace(/\./g, '').replace(',', '.').trim());
    };

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
        const { name, value } = e.target;
        if (name === 'valor') {
            setFormData(prev => ({ ...prev, [name]: formatCurrencyInput(value) }));
        } else {
            setFormData(prev => ({ ...prev, [name]: value }));
        }
    };

    const submitEntrada = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);
        try {
            await axios.post(`/api/campanhas/${campanhaId}/entradas`, {
                data: formData.data,
                valor: parseCurrency(formData.valor),
                forma: formData.forma,
                observacoes: formData.observacoes
            });
            setShowEntradaModal(false);
            resetForm();
            fetchData();
        } catch (error) {
            console.error("Error saving entrada", error);
            alert("Erro ao salvar entrada. Verifique os dados.");
        } finally {
            setIsSubmitting(false);
        }
    };

    const submitSaida = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);
        try {
            await axios.post(`/api/campanhas/${campanhaId}/saidas`, {
                data: formData.data,
                valor: parseCurrency(formData.valor),
                categoria: formData.categoria,
                descricao: formData.descricao
            });
            setShowSaidaModal(false);
            resetForm();
            fetchData();
        } catch (error) {
            console.error("Error saving saida", error);
            alert("Erro ao salvar saída. Verifique os dados.");
        } finally {
            setIsSubmitting(false);
        }
    };

    const resetForm = () => {
        setFormData({
            data: format(new Date(), 'yyyy-MM-dd'),
            valor: '',
            forma: '',
            categoria: '',
            observacoes: '',
            descricao: '',
        });
    };

    const handleDelete = (type: 'entrada' | 'saida', id: number) => {
        setDeleteTarget({ type, id });
        setShowDeleteConfirm(true);
    };

    const confirmDelete = async () => {
        if (!deleteTarget) return;
        setIsSubmitting(true);
        try {
            await axios.delete(`/api/campanhas/${deleteTarget.type}s/${deleteTarget.id}`);
            fetchData();
            setShowDeleteConfirm(false);
            setDeleteTarget(null);
        } catch (error) {
            console.error("Error deleting", error);
            alert("Erro ao excluir registro.");
        } finally {
            setIsSubmitting(false);
        }
    };

    const formatCurrencyValue = (val: number) => {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val);
    };

    const chartData = data ? [
        { name: 'Entradas', valor: Number(data.stats.total_arrecadado) },
        { name: 'Saídas', valor: Number(data.stats.total_gasto) },
    ] : [];

    const tableData = data ? (
        activeTab === 'entradas' ? data.entradas.map(e => ({...e, tipo: 'entrada'})) :
        activeTab === 'saidas' ? data.saidas.map(s => ({...s, tipo: 'saida'})) :
        data.movimentacoes
    ) : [];

    if (!isOpen) return null;

    return (
        <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1050 }} tabIndex={-1}>
            <div className="modal-dialog modal-fullscreen modal-dialog-centered modal-dialog-scrollable">
                <div className="modal-content rounded-0 border-0 shadow-lg">
                    <div className="modal-header border-bottom-0">
                        <div>
                            <h5 className="modal-title fw-bold">{loading || !data ? 'Carregando...' : data.campanha.nome}</h5>
                            <small className="text-muted">{paroquiaName} {data && `• ${data.campanha.status}`}</small>
                        </div>
                        <div className="d-flex gap-2 ms-auto me-3">
                             {!loading && data && (
                                <>
                                    <a href={`/campanhas/${campanhaId}/report`} className="btn btn-sm btn-danger rounded-pill d-flex align-items-center gap-2" target="_blank" rel="noopener noreferrer">
                                        <Download size={16} /> PDF Relatório
                                    </a>
                                    <button className="btn btn-sm btn-success rounded-pill d-flex align-items-center gap-2" onClick={() => setShowEntradaModal(true)}>
                                        <ArrowUpCircle size={16} /> Lançar Entrada
                                    </button>
                                    <button className="btn btn-sm btn-warning text-dark rounded-pill d-flex align-items-center gap-2" onClick={() => setShowSaidaModal(true)}>
                                        <ArrowDownCircle size={16} /> Lançar Saída
                                    </button>
                                </>
                            )}
                        </div>
                        <button type="button" className="btn-close" onClick={onClose}></button>
                    </div>
                    <div className="modal-body p-4 bg-light">
                        {loading ? (
                            <div className="d-flex justify-content-center align-items-center h-100">
                                <div className="spinner-border text-primary" role="status"></div>
                            </div>
                        ) : !data ? (
                            <div className="text-center p-5 text-danger">Erro ao carregar dados.</div>
                        ) : (
                            <div className="container-fluid">
                                {/* Stats Cards */}
                                <div className="row g-4 mb-4">
                                    <div className="col-md-4">
                                        <div className="card border-0 shadow-sm rounded-4 h-100 bg-success bg-opacity-10">
                                            <div className="card-body p-4">
                                                <h6 className="text-success text-uppercase fw-bold small mb-2">Total Arrecadado</h6>
                                                <h2 className="mb-0 fw-bold text-success">{formatCurrencyValue(data.stats.total_arrecadado)}</h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="col-md-4">
                                        <div className="card border-0 shadow-sm rounded-4 h-100 bg-danger bg-opacity-10">
                                            <div className="card-body p-4">
                                                <h6 className="text-danger text-uppercase fw-bold small mb-2">Total Gasto</h6>
                                                <h2 className="mb-0 fw-bold text-danger">{formatCurrencyValue(data.stats.total_gasto)}</h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="col-md-4">
                                        <div className="card border-0 shadow-sm rounded-4 h-100 bg-primary bg-opacity-10">
                                            <div className="card-body p-4">
                                                <h6 className="text-primary text-uppercase fw-bold small mb-2">Saldo Atual</h6>
                                                <h2 className="mb-0 fw-bold text-primary">{formatCurrencyValue(data.stats.saldo)}</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Chart & Tables */}
                                <div className="row g-4">
                                    <div className="col-lg-4">
                                        <div className="card border-0 shadow-sm rounded-4 h-100">
                                            <div className="card-body p-4">
                                                <h6 className="fw-bold mb-4">Balanço Financeiro</h6>
                                                <div style={{ height: '300px' }}>
                                                    <ResponsiveContainer width="100%" height="100%">
                                                        <BarChart data={chartData}>
                                                            <CartesianGrid strokeDasharray="3 3" vertical={false} />
                                                            <XAxis dataKey="name" axisLine={false} tickLine={false} />
                                                            <YAxis axisLine={false} tickLine={false} tickFormatter={(val) => `R$ ${val}`} />
                                                            <Tooltip formatter={(value: any) => formatCurrencyValue(Number(value))} cursor={{fill: 'transparent'}} />
                                                            <Bar dataKey="valor" radius={[10, 10, 0, 0]}>
                                                                {chartData.map((entry, index) => (
                                                                    <Cell key={`cell-${index}`} fill={index === 0 ? '#198754' : '#dc3545'} />
                                                                ))}
                                                            </Bar>
                                                        </BarChart>
                                                    </ResponsiveContainer>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="col-lg-8">
                                        <div className="card border-0 shadow-sm rounded-4 h-100">
                                            <div className="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                                                <ul className="nav nav-pills card-header-pills bg-light rounded-pill p-1 d-inline-flex">
                                                    <li className="nav-item">
                                                        <button 
                                                            className={`nav-link rounded-pill px-4 ${activeTab === 'completo' ? 'active shadow-sm' : 'text-muted'}`}
                                                            onClick={() => setActiveTab('completo')}
                                                        >
                                                            Movimentação Completa
                                                        </button>
                                                    </li>
                                                    <li className="nav-item">
                                                        <button 
                                                            className={`nav-link rounded-pill px-4 ${activeTab === 'entradas' ? 'active shadow-sm' : 'text-muted'}`}
                                                            onClick={() => setActiveTab('entradas')}
                                                        >
                                                            Entradas
                                                        </button>
                                                    </li>
                                                    <li className="nav-item">
                                                        <button 
                                                            className={`nav-link rounded-pill px-4 ${activeTab === 'saidas' ? 'active shadow-sm' : 'text-muted'}`}
                                                            onClick={() => setActiveTab('saidas')}
                                                        >
                                                            Saídas
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div className="card-body p-4">
                                                <div className="table-responsive">
                                                    <table className="table table-hover align-middle">
                                                        <thead className="text-muted small text-uppercase">
                                                            <tr>
                                                                <th className="border-0">Data</th>
                                                                {activeTab === 'completo' && <th className="border-0">Tipo</th>}
                                                                <th className="border-0">Valor</th>
                                                                <th className="border-0">Categoria/Forma</th>
                                                                <th className="border-0">Descrição/Obs</th>
                                                                <th className="border-0 text-end">Ações</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {tableData.map((item: any) => (
                                                                <tr key={`${item.tipo || (activeTab === 'entradas' ? 'entrada' : 'saida')}-${item.id}`}>
                                                                    <td>{format(new Date(item.data), 'dd/MM/yyyy')}</td>
                                                                    {activeTab === 'completo' && (
                                                                        <td>
                                                                            <span className={`badge bg-${item.tipo === 'entrada' ? 'success' : 'danger'} bg-opacity-10 text-${item.tipo === 'entrada' ? 'success' : 'danger'}`}>
                                                                                {item.tipo === 'entrada' ? 'Entrada' : 'Saída'}
                                                                            </span>
                                                                        </td>
                                                                    )}
                                                                    <td className={`fw-bold ${item.tipo === 'saida' || activeTab === 'saidas' ? 'text-danger' : 'text-success'}`}>
                                                                        {item.tipo === 'saida' || activeTab === 'saidas' ? '-' : '+'} {formatCurrencyValue(Number(item.valor))}
                                                                    </td>
                                                                    <td>{item.categoria || item.forma || '-'}</td>
                                                                    <td className="text-muted small">{item.descricao || item.observacoes || '-'}</td>
                                                                    <td className="text-end">
                                                                        <button className="btn btn-sm btn-link text-danger p-0" onClick={() => handleDelete(item.tipo || (activeTab === 'entradas' ? 'entrada' : 'saida'), item.id)}>
                                                                            <Trash2 size={16} />
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            ))}
                                                            {tableData.length === 0 && (
                                                                <tr>
                                                                    <td colSpan={6} className="text-center py-5 text-muted">
                                                                        <i className="bi bi-inbox fs-1 d-block mb-2"></i>
                                                                        Nenhum registro encontrado.
                                                                    </td>
                                                                </tr>
                                                            )}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Modal Entrada */}
            {showEntradaModal && (
                <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1060 }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content rounded-4 border-0 shadow">
                            <div className="modal-header border-0">
                                <h5 className="modal-title fw-bold text-success">Lançar Entrada</h5>
                                <button type="button" className="btn-close" onClick={() => setShowEntradaModal(false)}></button>
                            </div>
                            <form onSubmit={submitEntrada}>
                                <div className="modal-body">
                                    <div className="row g-3">
                                        <div className="col-6">
                                            <label className="form-label">Data</label>
                                            <input type="date" name="data" className="form-control" value={formData.data} onChange={handleInputChange} required />
                                        </div>
                                        <div className="col-6">
                                            <label className="form-label">Valor</label>
                                            <div className="input-group">
                                                <span className="input-group-text">R$</span>
                                                <input type="text" name="valor" className="form-control" value={formData.valor} onChange={handleInputChange} required />
                                            </div>
                                        </div>
                                        <div className="col-12">
                                            <label className="form-label">Forma de Pagamento</label>
                                            <select name="forma" className="form-select" value={formData.forma} onChange={handleInputChange} required>
                                                <option value="">Selecione...</option>
                                                <option value="Dinheiro">Dinheiro</option>
                                                <option value="Pix">Pix</option>
                                                <option value="Cartão">Cartão</option>
                                                <option value="Transferência">Transferência</option>
                                                <option value="Cheque">Cheque</option>
                                            </select>
                                        </div>
                                        <div className="col-12">
                                            <label className="form-label">Observações</label>
                                            <textarea name="observacoes" className="form-control" rows={3} value={formData.observacoes} onChange={handleInputChange}></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div className="modal-footer border-0">
                                    <button type="button" className="btn btn-light rounded-pill" onClick={() => setShowEntradaModal(false)} disabled={isSubmitting}>Cancelar</button>
                                    <button type="submit" className="btn btn-success rounded-pill px-4" disabled={isSubmitting}>
                                        {isSubmitting ? (
                                            <>
                                                <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                Salvando...
                                            </>
                                        ) : (
                                            'Salvar Entrada'
                                        )}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}

            {/* Modal Saida */}
            {showSaidaModal && (
                <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1060 }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content rounded-4 border-0 shadow">
                            <div className="modal-header border-0">
                                <h5 className="modal-title fw-bold text-danger">Lançar Saída</h5>
                                <button type="button" className="btn-close" onClick={() => setShowSaidaModal(false)}></button>
                            </div>
                            <form onSubmit={submitSaida}>
                                <div className="modal-body">
                                    <div className="row g-3">
                                        <div className="col-6">
                                            <label className="form-label">Data</label>
                                            <input type="date" name="data" className="form-control" value={formData.data} onChange={handleInputChange} required />
                                        </div>
                                        <div className="col-6">
                                            <label className="form-label">Valor</label>
                                            <div className="input-group">
                                                <span className="input-group-text">R$</span>
                                                <input type="text" name="valor" className="form-control" value={formData.valor} onChange={handleInputChange} required />
                                            </div>
                                        </div>
                                        <div className="col-12">
                                            <label className="form-label">Categoria de Gasto</label>
                                            <input type="text" name="categoria" className="form-control" placeholder="Ex: Material, Mão de obra..." value={formData.categoria} onChange={handleInputChange} required />
                                        </div>
                                        <div className="col-12">
                                            <label className="form-label">Descrição</label>
                                            <textarea name="descricao" className="form-control" rows={3} value={formData.descricao} onChange={handleInputChange}></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div className="modal-footer border-0">
                                    <button type="button" className="btn btn-light rounded-pill" onClick={() => setShowSaidaModal(false)} disabled={isSubmitting}>Cancelar</button>
                                    <button type="submit" className="btn btn-danger rounded-pill px-4" disabled={isSubmitting}>
                                        {isSubmitting ? (
                                            <>
                                                <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                Salvando...
                                            </>
                                        ) : (
                                            'Salvar Saída'
                                        )}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}

            {/* Delete Confirmation Modal */}
            {showDeleteConfirm && (
                <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1070 }} tabIndex={-1}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content rounded-4 border-0 shadow">
                            <div className="modal-header border-bottom-0 pb-0">
                                <h5 className="modal-title fw-bold text-danger">Excluir Movimentação</h5>
                                <button type="button" className="btn-close" onClick={() => setShowDeleteConfirm(false)}></button>
                            </div>
                            <div className="modal-body">
                                <p>Tem certeza que deseja excluir esta movimentação?</p>
                                <p className="text-muted small mb-0">Esta ação não pode ser desfeita e afetará o saldo da campanha.</p>
                            </div>
                            <div className="modal-footer border-top-0 pt-0">
                                <button type="button" className="btn btn-light rounded-pill" onClick={() => setShowDeleteConfirm(false)} disabled={isSubmitting}>Cancelar</button>
                                <button type="button" className="btn btn-danger rounded-pill px-4" onClick={confirmDelete} disabled={isSubmitting}>
                                    {isSubmitting ? (
                                        <>
                                            <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Excluindo...
                                        </>
                                    ) : (
                                        'Excluir'
                                    )}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
