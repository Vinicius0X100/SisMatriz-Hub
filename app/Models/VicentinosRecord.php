<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VicentinosRecord extends Model
{
    use HasFactory, \App\Traits\LogsActivity;

    public string $auditModuleName = 'Ações Sociais - Fichas';

    /**
     * Lista branca restrita à identificação e status administrativo.
     * Dados de saúde, religião e observações íntimas são omitidos por LGPD.
     */
    public array $auditableAttributes = [
        'responsavel_nome',
        'cpf',
        'rg',
        'telefone',
        'bairro',
        'cidade',
        'equipe_id',
        'data_ficha',
        'motivo_dispensa',
        'data_dispensa',
        'paroquia_id',
    ];

    protected $fillable = [
        'user_id',
        'paroquia_id',
        'equipe_id',
        'register_id',
        'data_ficha',
        'conferencia',
        'conselho_particular',
        'responsavel_nome',
        'data_nascimento',
        'idade',
        'sexo',
        'rg',
        'cpf',
        'telefone',
        'endereco',
        'endereco_numero',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'contato_principal',
        'contato_recado',
        'falar_com',
        'recebe_bolsa_familia',
        'valor_bolsa_familia',
        'outro_beneficio_nome',
        'outro_beneficio_valor',
        'tipo_residencia',
        'valor_aluguel_prestacao',
        'religiao',
        'catolico_tem_sacramentos',
        'sacramento_faltando',
        'quem_trabalha',
        'local_trabalho',
        'observacoes',
        'responsaveis_sindicancia',
        'motivo_dispensa',
        'data_dispensa',
    ];

    protected $casts = [
        'data_ficha' => 'date',
        'data_nascimento' => 'date',
        'data_dispensa' => 'date',
        'recebe_bolsa_familia' => 'boolean',
        'catolico_tem_sacramentos' => 'boolean',
        'valor_bolsa_familia' => 'decimal:2',
        'outro_beneficio_valor' => 'decimal:2',
        'valor_aluguel_prestacao' => 'decimal:2',
    ];

    public function families()
    {
        return $this->hasMany(VicentinosFamily::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paroquia()
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id'); // Assuming ParoquiaSuperadmin is the model for paroquias
    }

    public function register()
    {
        return $this->belongsTo(Register::class);
    }

    public function equipe()
    {
        return $this->belongsTo(AcaoSocialEquipe::class, 'equipe_id');
    }
}
