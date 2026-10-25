<?php

namespace Tests\Feature;

use App\Models\AcaoSocialEquipe;
use App\Models\Entidade;
use App\Models\ParoquiaSuperadmin;
use App\Models\User;
use App\Models\VicentinosFamily;
use App\Models\VicentinosRecord;
use App\Models\VinWatched;
use App\Services\AcaoSocialContextService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AcoesSociaisTest extends TestCase
{
    use DatabaseTransactions;

    protected ParoquiaSuperadmin $paroquia;
    protected ParoquiaSuperadmin $outraParoquia;
    protected Entidade $entidade;

    protected function setUp(): void
    {
        parent::setUp();

        // Paróquia principal de teste
        $this->paroquia = ParoquiaSuperadmin::find(1) ?? ParoquiaSuperadmin::first();

        // Segunda paróquia para validação multi-tenant
        $this->outraParoquia = ParoquiaSuperadmin::where('id', '!=', $this->paroquia->id)->first() ?? ParoquiaSuperadmin::create([
            'name' => 'Paróquia Outra ' . uniqid(),
            'slug' => 'outra-' . uniqid(),
            'status' => 1,
        ]);

        // Entidade/Comunidade da paróquia principal
        $this->entidade = Entidade::where('paroquia_id', $this->paroquia->id)->first() ?? Entidade::create([
            'ent_name' => 'Comunidade Matriz',
            'paroquia_id' => $this->paroquia->id,
            'address' => 'Rua da Matriz, 100',
        ]);
    }

    /**
     * Helper para criar um usuário associado a uma paróquia
     */
    protected function makeUser(ParoquiaSuperadmin $paroquia, string $rule = '4', string $name = 'Usuario Teste'): User
    {
        return User::create([
            'name' => $name,
            'user' => 'user_' . uniqid(),
            'email' => 'user_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'rule' => $rule,
            'status' => 1,
            'paroquia_id' => $paroquia->id,
            'is_pass_change' => 1,
            'user_consent' => 1,
            'accepted_photo' => 1,
        ]);
    }

    /**
     * Helper para criar uma equipe de ações sociais
     */
    protected function makeEquipe(ParoquiaSuperadmin $paroquia, string $nome, string $tipo = 'geral'): AcaoSocialEquipe
    {
        return AcaoSocialEquipe::create([
            'paroquia_id' => $paroquia->id,
            'nome' => $nome,
            'tipo' => $tipo,
            'ativo' => true,
        ]);
    }

    /**
     * CENÁRIO A: Paróquia com apenas 1 equipe autorizada.
     * A seleção deve ocorrer automaticamente e de forma transparente.
     */
    public function test_cenario_a_selecao_automatica_quando_ha_apenas_uma_equipe_autorizada(): void
    {
        $equipe = $this->makeEquipe($this->paroquia, 'Pastoral da Promoção Humana', 'promocao_humana');
        $user = $this->makeUser($this->paroquia, '4', 'Agente Ana');
        $user->acaoSocialEquipes()->attach($equipe->id, [
            'funcao' => 'agente',
            'paroquia_id' => $this->paroquia->id,
        ]);

        $service = app(AcaoSocialContextService::class);
        $equipeAtiva = $service->getActiveEquipe($user);

        $this->assertNotNull($equipeAtiva);
        $this->assertEquals($equipe->id, $equipeAtiva->id);
        $this->assertEquals('Pastoral da Promoção Humana', $equipeAtiva->nome);

        // Acessa as páginas web e verifica que renderizam 200 com contexto da equipe
        $response = $this->actingAs($user)->get(route('acoes-sociais.familias.index'));
        $response->assertStatus(200);
        $response->assertSee('Pastoral da Promoção Humana');

        $responseAtendimentos = $this->actingAs($user)->get(route('acoes-sociais.atendimentos.index'));
        $responseAtendimentos->assertStatus(200);
        $responseAtendimentos->assertSee('Pastoral da Promoção Humana');
    }

    /**
     * CENÁRIO B: Paróquia com múltiplas equipes.
     * Isolamento estrito entre equipes da mesma paróquia:
     * Usuário da Equipe A não acessa dados da Equipe B.
     */
    public function test_cenario_b_isolamento_estrito_entre_equipes_da_mesma_paroquia(): void
    {
        $equipeA = $this->makeEquipe($this->paroquia, 'Conferência Vicentina São José', 'vicentinos');
        $equipeB = $this->makeEquipe($this->paroquia, 'Pastoral da Criança e Família', 'promocao_humana');

        $userA = $this->makeUser($this->paroquia, '4', 'Agente Equipe A');
        $userA->acaoSocialEquipes()->attach($equipeA->id, [
            'funcao' => 'agente',
            'paroquia_id' => $this->paroquia->id,
        ]);

        // Cria registro na Equipe A
        $registroA = VicentinosRecord::create([
            'paroquia_id' => $this->paroquia->id,
            'equipe_id' => $equipeA->id,
            'user_id' => $userA->id,
            'responsavel_nome' => 'Família Santos Equipe A',
            'cpf' => '111.222.333-44',
            'telefone' => '11999990001',
            'endereco' => 'Rua A, 10',
            'bairro' => 'Centro',
            'cidade' => 'São Paulo',
        ]);

        // Cria registro na Equipe B
        $registroB = VicentinosRecord::create([
            'paroquia_id' => $this->paroquia->id,
            'equipe_id' => $equipeB->id,
            'user_id' => $userA->id,
            'responsavel_nome' => 'Família Oliveira Equipe B',
            'cpf' => '555.666.777-88',
            'telefone' => '11999990002',
            'endereco' => 'Rua B, 20',
            'bairro' => 'Bairro B',
            'cidade' => 'São Paulo',
        ]);

        // Usuário A listando famílias: deve ver Família Santos e NÃO deve ver Família Oliveira
        $response = $this->actingAs($userA)->getJson(route('acoes-sociais.familias.index'));
        $response->assertStatus(200);
        $response->assertJsonFragment(['responsavel_nome' => 'Família Santos Equipe A']);
        $response->assertJsonMissing(['responsavel_nome' => 'Família Oliveira Equipe B']);

        // Usuário A tentando acessar o registro B diretamente deve receber 404 (isolamento estrito)
        $responseDirect = $this->actingAs($userA)->get(route('acoes-sociais.familias.show', $registroB->id));
        $responseDirect->assertStatus(404);

        $responseEdit = $this->actingAs($userA)->get(route('acoes-sociais.familias.edit', $registroB->id));
        $responseEdit->assertStatus(404);

        // Usuário A tentando alternar para a Equipe B (sem autorização) deve ser bloqueado
        $responseSwitch = $this->actingAs($userA)->from('/acoes-sociais/familias')->post(route('acoes-sociais.switch-equipe'), [
            'equipe_id' => $equipeB->id,
        ]);
        $responseSwitch->assertSessionHas('error');

        // Contexto permanece na Equipe A
        $service = app(AcaoSocialContextService::class);
        $this->assertEquals($equipeA->id, $service->getActiveEquipe($userA)->id);
    }

    /**
     * AUTORIZAÇÃO: Usuário sem equipe vinculada não tem resolução de contexto
     * e não pode visualizar nem vazar registros.
     */
    public function test_usuario_sem_equipe_autorizada_nao_contorna_permissoes(): void
    {
        $equipe = $this->makeEquipe($this->paroquia, 'Equipe Geral', 'geral');
        $userSemEquipe = $this->makeUser($this->paroquia, '4', 'Sem Equipe');

        // Registro criado na paróquia
        VicentinosRecord::create([
            'paroquia_id' => $this->paroquia->id,
            'equipe_id' => $equipe->id,
            'user_id' => $userSemEquipe->id,
            'responsavel_nome' => 'Família Confidencial',
        ]);

        $service = app(AcaoSocialContextService::class);
        $this->assertNull($service->getActiveEquipe($userSemEquipe));

        // Resposta da view deve indicar que nenhuma equipe foi selecionada/autorizada
        $response = $this->actingAs($userSemEquipe)->get(route('acoes-sociais.familias.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Família Confidencial');

        // API também deve retornar lista vazia
        $responseApi = $this->actingAs($userSemEquipe)->getJson('/api/vicentinos/fichas');
        $responseApi->assertStatus(200);
        $responseApi->assertJsonPath('total', 0);
        $responseApi->assertJsonPath('data', []);
    }

    /**
     * ADMINISTRADOR: Usuário com perfil 1 ou 111 pode acessar todas as equipes da sua paróquia
     * e alternar livremente entre elas.
     */
    public function test_administrador_pode_acessar_e_alternar_entre_qualquer_equipe_da_paroquia(): void
    {
        $equipe1 = $this->makeEquipe($this->paroquia, 'Equipe Alfa', 'vicentinos');
        $equipe2 = $this->makeEquipe($this->paroquia, 'Equipe Beta', 'promocao_humana');

        $admin = $this->makeUser($this->paroquia, '1', 'Padre Administrador');

        // O admin vê todas as equipes ativas da paróquia
        $service = app(AcaoSocialContextService::class);
        $equipesDisponiveis = $service->getAuthorizedEquipes($admin);
        $this->assertGreaterThanOrEqual(2, $equipesDisponiveis->count());

        // Alterna para Equipe Beta
        $response = $this->actingAs($admin)->from('/acoes-sociais/familias')->post(route('acoes-sociais.switch-equipe'), [
            'equipe_id' => $equipe2->id,
        ]);
        $response->assertSessionHas('success');

        $this->assertEquals($equipe2->id, $service->getActiveEquipe($admin)->id);
    }

    /**
     * MULTI-TENANT: Isolamento estrito entre paróquias diferentes.
     */
    public function test_isolamento_multitenant_entre_paroquias(): void
    {
        $equipeParoquia1 = $this->makeEquipe($this->paroquia, 'Equipe P1');
        $equipeParoquia2 = $this->makeEquipe($this->outraParoquia, 'Equipe P2');

        $user1 = $this->makeUser($this->paroquia, '4');
        $user1->acaoSocialEquipes()->attach($equipeParoquia1->id, [
            'funcao' => 'agente',
            'paroquia_id' => $this->paroquia->id,
        ]);

        $user2 = $this->makeUser($this->outraParoquia, '4');
        $user2->acaoSocialEquipes()->attach($equipeParoquia2->id, [
            'funcao' => 'agente',
            'paroquia_id' => $this->outraParoquia->id,
        ]);

        VicentinosRecord::create([
            'paroquia_id' => $this->paroquia->id,
            'equipe_id' => $equipeParoquia1->id,
            'user_id' => $user1->id,
            'responsavel_nome' => 'Família Exclusiva P1',
        ]);

        // User 2 não deve ver registros de P1
        $response = $this->actingAs($user2)->get(route('acoes-sociais.familias.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Família Exclusiva P1');

        // User 1 não consegue alternar para equipe de P2
        $responseSwitch = $this->actingAs($user1)->from('/acoes-sociais/familias')->post(route('acoes-sociais.switch-equipe'), [
            'equipe_id' => $equipeParoquia2->id,
        ]);
        $responseSwitch->assertSessionHas('error');
    }

    /**
     * CONTRATO JSON DA API LEGADA: /api/vicentinos/fichas
     * Garante compatibilidade 100% com os apps legados já publicados.
     */
    public function test_contrato_json_completo_api_legada_vicentinos_fichas(): void
    {
        $equipe = $this->makeEquipe($this->paroquia, 'Equipe Móvel', 'vicentinos');
        $user = $this->makeUser($this->paroquia, '4', 'Agente Mobile');
        $user->acaoSocialEquipes()->attach($equipe->id, [
            'funcao' => 'agente',
            'paroquia_id' => $this->paroquia->id,
        ]);

        $registro = VicentinosRecord::create([
            'paroquia_id' => $this->paroquia->id,
            'equipe_id' => $equipe->id,
            'user_id' => $user->id,
            'responsavel_nome' => 'Carlos Alberto Silva',
            'cpf' => '123.456.789-00',
            'rg' => 'MG-12.345.678',
            'telefone' => '31988887777',
            'endereco' => 'Avenida Principal',
            'endereco_numero' => '500',
            'bairro' => 'Centro',
            'cidade' => 'Belo Horizonte',
            'estado' => 'MG',
            'cep' => '30100-000',
            'recebe_bolsa_familia' => true,
            'valor_bolsa_familia' => 600.00,
        ]);

        // Adiciona membro familiar
        VicentinosFamily::create([
            'vicentinos_record_id' => $registro->id,
            'user_id' => $user->id,
            'paroquia_id' => $this->paroquia->id,
            'nome' => 'Maria Silva',
            'parentesco' => 'Esposa',
            'nascimento' => '1985-05-10',
            'profissao' => 'Autônoma',
            'renda' => 800.00,
        ]);

        // Faz requisição autenticada ao endpoint legado
        $response = $this->actingAs($user)->getJson('/api/vicentinos/fichas');

        $response->assertStatus(200);

        // Validação da paginação do contrato LengthAwarePaginator
        $response->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => [
                    'id',
                    'paroquia_id',
                    'equipe_id',
                    'responsavel_nome',
                    'cpf',
                    'rg',
                    'telefone',
                    'endereco',
                    'bairro',
                    'cidade',
                    'recebe_bolsa_familia',
                    'valor_bolsa_familia',
                    'created_at',
                ]
            ],
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
            'links',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total',
        ]);

        $response->assertJsonPath('total', 1);
        $response->assertJsonPath('data.0.responsavel_nome', 'Carlos Alberto Silva');
        $response->assertJsonPath('data.0.cpf', '123.456.789-00');

        // Teste de filtro por busca
        $responseFilterMatch = $this->actingAs($user)->getJson('/api/vicentinos/fichas?search=Carlos');
        $responseFilterMatch->assertJsonPath('total', 1);

        $responseFilterNoMatch = $this->actingAs($user)->getJson('/api/vicentinos/fichas?search=Inexistente');
        $responseFilterNoMatch->assertJsonPath('total', 0);
    }

    /**
     * CONTRATO JSON DA API LEGADA: /api/vicentinos/apuracoes
     * Garante compatibilidade 100% com os apps legados já publicados.
     */
    public function test_contrato_json_completo_api_legada_vicentinos_apuracoes(): void
    {
        $equipe = $this->makeEquipe($this->paroquia, 'Equipe Apuração', 'vicentinos');
        $user = $this->makeUser($this->paroquia, '4', 'Agente Apuração');
        $user->acaoSocialEquipes()->attach($equipe->id, [
            'funcao' => 'agente',
            'paroquia_id' => $this->paroquia->id,
        ]);

        $atendimento = VinWatched::create([
            'paroquia_id' => $this->paroquia->id,
            'equipe_id' => $equipe->id,
            'sendby' => $user->id,
            'ent_id' => $this->entidade->ent_id,
            'name' => 'Família Pereira Atendida',
            'address' => 'Rua das Flores',
            'address_number' => '123',
            'month_entire' => 10,
            'kind' => 1, // Assistido
            'description' => 'Entrega de cesta básica mensal.',
            'created_at' => now(),
        ]);

        // Faz requisição autenticada ao endpoint legado
        $response = $this->actingAs($user)->getJson('/api/vicentinos/apuracoes');

        $response->assertStatus(200);

        // Validação da paginação e contrato JSON
        $response->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => [
                    'w_id',
                    'paroquia_id',
                    'equipe_id',
                    'name',
                    'address',
                    'address_number',
                    'month_entire',
                    'kind',
                    'description',
                    'ent_id',
                    'sendby',
                    'created_at',
                ]
            ],
            'per_page',
            'total',
            'last_page',
        ]);

        $response->assertJsonPath('total', 1);
        $response->assertJsonPath('data.0.name', 'Família Pereira Atendida');
        $response->assertJsonPath('data.0.kind', 1);

        // Teste de filtro por kind (Assistido = 1 vs Não Assistido = 0)
        $responseKindMatch = $this->actingAs($user)->getJson('/api/vicentinos/apuracoes?kind=1');
        $responseKindMatch->assertJsonPath('total', 1);

        $responseKindNoMatch = $this->actingAs($user)->getJson('/api/vicentinos/apuracoes?kind=0');
        $responseKindNoMatch->assertJsonPath('total', 0);
    }

    /**
     * ROTAS CANÔNICAS DA API: /api/acoes-sociais/*
     * Devem retornar os mesmos dados das rotas legadas.
     */
    public function test_rotas_canonicas_da_api_acoes_sociais(): void
    {
        $equipe = $this->makeEquipe($this->paroquia, 'Equipe Universal', 'geral');
        $user = $this->makeUser($this->paroquia, '4');
        $user->acaoSocialEquipes()->attach($equipe->id, [
            'funcao' => 'agente',
            'paroquia_id' => $this->paroquia->id,
        ]);

        VicentinosRecord::create([
            'paroquia_id' => $this->paroquia->id,
            'equipe_id' => $equipe->id,
            'user_id' => $user->id,
            'responsavel_nome' => 'Família Canônica',
        ]);

        VinWatched::create([
            'paroquia_id' => $this->paroquia->id,
            'equipe_id' => $equipe->id,
            'sendby' => $user->id,
            'ent_id' => $this->entidade->ent_id,
            'name' => 'Atendimento Canônico',
            'kind' => 1,
            'month_entire' => 10,
            'created_at' => now(),
        ]);

        // Famílias
        $responseFamilias = $this->actingAs($user)->getJson('/api/acoes-sociais/familias');
        $responseFamilias->assertStatus(200);
        $responseFamilias->assertJsonPath('total', 1);
        $responseFamilias->assertJsonPath('data.0.responsavel_nome', 'Família Canônica');

        // Atendimentos
        $responseAtendimentos = $this->actingAs($user)->getJson('/api/acoes-sociais/atendimentos');
        $responseAtendimentos->assertStatus(200);
        $responseAtendimentos->assertJsonPath('total', 1);
        $responseAtendimentos->assertJsonPath('data.0.name', 'Atendimento Canônico');
    }

    /**
     * SEGURANÇA DA API: Bloqueio para requisições não autenticadas
     */
    public function test_api_requer_autenticacao(): void
    {
        $responseFichas = $this->getJson('/api/vicentinos/fichas');
        // Unauthenticated json request should return 401
        $responseFichas->assertStatus(401);

        $responseApuracoes = $this->getJson('/api/vicentinos/apuracoes');
        $responseApuracoes->assertStatus(401);
    }

    /**
     * PRESERVAÇÃO DO CARGO: O cargo 4 permanece com o identificador '4'
     * e o rótulo atualizado para 'Agente de Ações Sociais'.
     */
    public function test_cargo_4_permanece_preservado_e_identificado(): void
    {
        $this->assertArrayHasKey(4, User::ROLE_LABELS);
        $this->assertEquals('Agente de Ações Sociais', User::ROLE_LABELS[4]);

        $user = $this->makeUser($this->paroquia, '4', 'Agente Social');
        $this->assertTrue($user->hasAnyRole(['4']));
        $this->assertFalse($user->hasAnyRole(['1', '111']));
    }

    /**
     * VINCULAÇÃO OPCIONAL ENTRE ATENDIMENTO E FAMÍLIA:
     * Campo vicentinos_record_id na tabela vin_watcheds.
     */
    public function test_vinculacao_opcional_entre_atendimento_e_familia(): void
    {
        $equipe = $this->makeEquipe($this->paroquia, 'Equipe de Teste');
        $user = $this->makeUser($this->paroquia, '4');
        $user->acaoSocialEquipes()->attach($equipe->id, [
            'funcao' => 'agente',
            'paroquia_id' => $this->paroquia->id,
        ]);

        $familia = VicentinosRecord::create([
            'paroquia_id' => $this->paroquia->id,
            'equipe_id' => $equipe->id,
            'user_id' => $user->id,
            'responsavel_nome' => 'Família Integrada',
        ]);

        $atendimento = VinWatched::create([
            'paroquia_id' => $this->paroquia->id,
            'equipe_id' => $equipe->id,
            'sendby' => $user->id,
            'ent_id' => $this->entidade->ent_id,
            'vicentinos_record_id' => $familia->id,
            'name' => 'Família Integrada',
            'kind' => 1,
            'month_entire' => 10,
            'created_at' => now(),
        ]);

        $this->assertNotNull($atendimento->familia);
        $this->assertEquals($familia->id, $atendimento->familia->id);
        $this->assertEquals('Família Integrada', $atendimento->familia->responsavel_nome);
    }
}
