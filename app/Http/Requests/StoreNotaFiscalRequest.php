<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class StoreNotaFiscalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'valor_total' => $this->cleanCurrency($this->valor_total),
            'valor_desconto' => $this->cleanCurrency($this->valor_desconto),
            'valor_acrescimo' => $this->cleanCurrency($this->valor_acrescimo),
        ]);
    }

    private function cleanCurrency($value)
    {
        if (empty($value)) {
            return null;
        }
        
        // Se já for numérico (ex: veio do banco ou não foi formatado), retorna como está
        if (is_numeric($value)) {
            return $value;
        }

        // Remove tudo que não for dígito ou vírgula
        $clean = preg_replace('/[^\d,]/', '', $value);
        
        // Troca vírgula por ponto
        return str_replace(',', '.', $clean);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'numero' => 'required|string|max:50',
            'serie' => 'nullable|string|max:10',
            'chave_acesso' => [
                'nullable',
                'string',
                'size:44',
                Rule::unique('notas_fiscais')->where(function ($query) {
                    return $query->where('paroquia_id', Auth::user()->paroquia_id);
                })->ignore($this->nota_fiscal)
            ],
            'tipo' => 'required|in:NFe,NFCe,NFSe,Cupom,Recibo,Boleto,Outro',
            'data_emissao' => 'required|date',
            'data_entrada' => 'nullable|date',
            'emitente_nome' => 'required|string|max:255',
            'emitente_documento' => 'nullable|string|max:20',
            'valor_total' => 'required|numeric|min:0',
            'valor_desconto' => 'nullable|numeric|min:0',
            'valor_acrescimo' => 'nullable|numeric|min:0',
            'descricao' => 'nullable|string',
            'entidade_id' => 'nullable|exists:entidades,ent_id',
            'arquivo' => 'nullable|file|mimes:pdf,xml,jpg,jpeg,png|max:10240', // 10MB Max
        ];
    }

    public function messages()
    {
        return [
            'chave_acesso.size' => 'A Chave de Acesso deve ter exatamente 44 dígitos.',
            'chave_acesso.unique' => 'Esta Chave de Acesso já foi cadastrada nesta paróquia.',
            'arquivo.max' => 'O arquivo não pode ser maior que 10MB.',
            'arquivo.mimes' => 'O arquivo deve ser PDF, XML ou Imagem (JPG, PNG).',
        ];
    }
}
