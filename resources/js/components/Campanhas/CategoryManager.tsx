import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { PieChart, Pie, Cell, BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer } from 'recharts';
import { Trash2, Edit2, Plus, X } from 'lucide-react';

interface Category {
    id: number;
    nome: string;
    campanhas_count?: number;
}

interface Stats {
    total: number;
    unused: number;
    most_used: string;
    bar_chart: any[];
    pie_chart: any[];
}

const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884d8'];

export default function CategoryManager({ isOpen, onClose, paroquiaName }: { isOpen: boolean; onClose: () => void; paroquiaName: string }) {
    const [categories, setCategories] = useState<Category[]>([]);
    const [stats, setStats] = useState<Stats | null>(null);
    const [newCategoryName, setNewCategoryName] = useState('');
    const [editingId, setEditingId] = useState<number | null>(null);
    const [editName, setEditName] = useState('');
    const [loading, setLoading] = useState(false);
    const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
    const [categoryToDelete, setCategoryToDelete] = useState<number | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);

    useEffect(() => {
        if (isOpen) {
            fetchData();
        }
    }, [isOpen]);

    const fetchData = async () => {
        setLoading(true);
        try {
            const [catsRes, statsRes] = await Promise.all([
                axios.get('/api/campanhas/categorias'),
                axios.get('/api/campanhas/categorias/stats')
            ]);
            setCategories(catsRes.data);
            setStats(statsRes.data);
        } catch (error) {
            console.error("Error fetching data", error);
        } finally {
            setLoading(false);
        }
    };

    const handleAdd = async () => {
        if (!newCategoryName.trim()) return;
        setIsSubmitting(true);
        try {
            await axios.post('/api/campanhas/categorias', { nome: newCategoryName });
            setNewCategoryName('');
            fetchData(); // Refresh list and stats
        } catch (error) {
            console.error("Error adding category", error);
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleEditStart = (cat: Category) => {
        setEditingId(cat.id);
        setEditName(cat.nome);
    };

    const handleEditSave = async (id: number) => {
        try {
            await axios.put(`/api/campanhas/categorias/${id}`, { nome: editName });
            setEditingId(null);
            fetchData();
        } catch (error) {
            console.error("Error updating category", error);
        }
    };

    const confirmDelete = (id: number) => {
        setCategoryToDelete(id);
        setShowDeleteConfirm(true);
    };

    const handleDelete = async () => {
        if (!categoryToDelete) return;
        
        setIsSubmitting(true);
        try {
            await axios.delete(`/api/campanhas/categorias/${categoryToDelete}`);
            fetchData();
            setShowDeleteConfirm(false);
            setCategoryToDelete(null);
        } catch (error) {
            console.error("Error deleting category", error);
        } finally {
            setIsSubmitting(false);
        }
    };

    if (!isOpen) return null;

    return (
        <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }} tabIndex={-1}>
            <div className="modal-dialog modal-fullscreen modal-dialog-centered modal-dialog-scrollable">
                <div className="modal-content rounded-0 border-0 shadow-lg">
                    <div className="modal-header border-bottom-0">
                        <div>
                            <h5 className="modal-title fw-bold">Gerenciar Categorias de Campanhas</h5>
                            <small className="text-muted">{paroquiaName}</small>
                        </div>
                        <button type="button" className="btn-close" onClick={onClose}></button>
                    </div>
                    <div className="modal-body p-4">
                        <div className="row h-100">
                            {/* Left Side: Management */}
                            <div className="col-md-5 border-end pe-4">
                                <h6 className="fw-bold mb-3 text-muted">Adicionar / Editar</h6>
                                <div className="input-group mb-4">
                                    <input 
                                        type="text" 
                                        className="form-control rounded-start-pill" 
                                        placeholder="Nova categoria..." 
                                        value={newCategoryName}
                                        onChange={(e) => setNewCategoryName(e.target.value)}
                                        onKeyDown={(e) => e.key === 'Enter' && handleAdd()}
                                        disabled={isSubmitting}
                                    />
                                    <button className="btn btn-primary rounded-end-pill px-4" onClick={handleAdd} disabled={isSubmitting}>
                                        {isSubmitting ? <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> : <Plus size={20} />}
                                    </button>
                                </div>

                                <div className="table-responsive" style={{ maxHeight: '60vh' }}>
                                    <table className="table table-hover align-middle">
                                        <thead className="table-light">
                                            <tr>
                                                <th className="rounded-start-3">Nome</th>
                                                <th className="rounded-end-3 text-end">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {categories.map(cat => (
                                                <tr key={cat.id}>
                                                    <td>
                                                        {editingId === cat.id ? (
                                                            <input 
                                                                type="text" 
                                                                className="form-control form-control-sm"
                                                                value={editName}
                                                                onChange={(e) => setEditName(e.target.value)}
                                                                onBlur={() => handleEditSave(cat.id)}
                                                                onKeyDown={(e) => e.key === 'Enter' && handleEditSave(cat.id)}
                                                                autoFocus
                                                            />
                                                        ) : (
                                                            <span className="fw-medium">{cat.nome}</span>
                                                        )}
                                                    </td>
                                                    <td className="text-end">
                                                        <button className="btn btn-sm btn-link text-muted p-0 me-2" onClick={() => handleEditStart(cat)}>
                                                            <Edit2 size={16} />
                                                        </button>
                                                        <button className="btn btn-sm btn-link text-danger p-0" onClick={() => confirmDelete(cat.id)}>
                                                            <Trash2 size={16} />
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {/* Right Side: Stats */}
                            <div className="col-md-7 ps-4">
                                <h6 className="fw-bold mb-3 text-muted">Estatísticas</h6>
                                
                                <div className="row g-3 mb-4">
                                    <div className="col-md-4">
                                        <div className="card border-0 bg-primary bg-opacity-10 h-100 rounded-4">
                                            <div className="card-body p-3">
                                                <div className="small text-uppercase fw-bold text-primary opacity-75">Total</div>
                                                <div className="fs-3 fw-bold text-primary">{stats?.total || 0}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="col-md-4">
                                        <div className="card border-0 bg-secondary bg-opacity-10 h-100 rounded-4">
                                            <div className="card-body p-3">
                                                <div className="small text-uppercase fw-bold text-secondary opacity-75">Sem Uso</div>
                                                <div className="fs-3 fw-bold text-secondary">{stats?.unused || 0}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="col-md-4">
                                        <div className="card border-0 bg-success bg-opacity-10 h-100 rounded-4">
                                            <div className="card-body p-3">
                                                <div className="small text-uppercase fw-bold text-success opacity-75">Mais Usada</div>
                                                <div className="fs-5 fw-bold text-success text-truncate" title={stats?.most_used}>{stats?.most_used || '-'}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="row g-4">
                                    <div className="col-md-6" style={{ height: '300px' }}>
                                        <h6 className="text-center small text-muted mb-2">Categorias Mais Usadas</h6>
                                        <ResponsiveContainer width="100%" height="100%">
                                            <BarChart data={stats?.bar_chart || []}>
                                                <XAxis dataKey="name" hide />
                                                <Tooltip />
                                                <Bar dataKey="count" fill="#0d6efd" radius={[4, 4, 0, 0]} />
                                            </BarChart>
                                        </ResponsiveContainer>
                                    </div>
                                    <div className="col-md-6" style={{ height: '300px' }}>
                                        <h6 className="text-center small text-muted mb-2">Distribuição</h6>
                                        <ResponsiveContainer width="100%" height="100%">
                                            <PieChart>
                                                <Pie
                                                    data={stats?.pie_chart || []}
                                                    innerRadius={60}
                                                    outerRadius={80}
                                                    paddingAngle={5}
                                                    dataKey="value"
                                                >
                                                    {(stats?.pie_chart || []).map((entry, index) => (
                                                        <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                                    ))}
                                                </Pie>
                                                <Tooltip />
                                            </PieChart>
                                        </ResponsiveContainer>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Delete Confirmation Modal */}
            {showDeleteConfirm && (
                <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1060 }} tabIndex={-1}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content rounded-4 border-0 shadow">
                            <div className="modal-header border-bottom-0 pb-0">
                                <h5 className="modal-title fw-bold text-danger">Excluir Categoria</h5>
                                <button type="button" className="btn-close" onClick={() => setShowDeleteConfirm(false)}></button>
                            </div>
                            <div className="modal-body">
                                <p>Tem certeza que deseja excluir esta categoria?</p>
                                <p className="text-muted small mb-0">Esta ação não pode ser desfeita e pode afetar campanhas associadas.</p>
                            </div>
                            <div className="modal-footer border-top-0 pt-0">
                                <button type="button" className="btn btn-light rounded-pill" onClick={() => setShowDeleteConfirm(false)} disabled={isSubmitting}>Cancelar</button>
                                <button type="button" className="btn btn-danger rounded-pill px-4" onClick={handleDelete} disabled={isSubmitting}>
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
