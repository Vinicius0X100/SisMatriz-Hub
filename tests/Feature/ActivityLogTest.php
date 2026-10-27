<?php

namespace Tests\Feature;

use App\Console\Commands\PruneActivityLogsCommand;
use App\Models\ActivityLog;
use App\Models\NotaFiscal;
use App\Models\ParoquiaSuperadmin;
use App\Models\User;
use App\Services\ActivityLoggerService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    protected ParoquiaSuperadmin $paroquia1;
    protected ParoquiaSuperadmin $paroquia2;
    protected User $adminParoquia1;
    protected User $adminGlobal111;
    protected User $usuarioComum;

    protected function setUp(): void
    {
        parent::setUp();

        // Configuração de SQLite em memória para isolamento total e velocidade nos testes
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        // Criação das tabelas mínimas necessárias em memória
        $this->createSchema();

        // Criação de Paróquias para validação multi-tenant
        $this->paroquia1 = ParoquiaSuperadmin::create([
            'id' => 1,
            'name' => 'Paróquia Matriz São José',
            'slug' => 'sao-jose',
            'status' => 1,
        ]);

        $this->paroquia2 = ParoquiaSuperadmin::create([
            'id' => 2,
            'name' => 'Paróquia Nossa Senhora Aparecida',
            'slug' => 'aparecida',
            'status' => 1,
        ]);

        // Usuário Administrador Geral da Paróquia 1 (Role 1)
        $this->adminParoquia1 = User::create([
            'name' => 'Padre Admin Paróquia 1',
            'user' => 'admin_paroquia_1',
            'email' => 'admin1@sismatriz.test',
            'password' => bcrypt('senha123'),
            'rule' => '1',
            'status' => 0, // Ativo
            'paroquia_id' => $this->paroquia1->id,
            'is_pass_change' => 1,
            'user_consent' => 1,
            'accepted_photo' => 1,
        ]);

        // Usuário Administrador Global do Sistema (Role 111)
        $this->adminGlobal111 = User::create([
            'name' => 'Superadmin Global',
            'user' => 'superadmin_111',
            'email' => 'super111@sismatriz.test',
            'password' => bcrypt('senha123'),
            'rule' => '111',
            'status' => 0,
            'paroquia_id' => $this->paroquia1->id,
            'is_pass_change' => 1,
            'user_consent' => 1,
            'accepted_photo' => 1,
        ]);

        // Usuário sem privilégios administrativos (ex: Agente de Ação Social - Role 4)
        $this->usuarioComum = User::create([
            'name' => 'Agente Social Comum',
            'user' => 'agente_social',
            'email' => 'agente@sismatriz.test',
            'password' => bcrypt('senha123'),
            'rule' => '4',
            'status' => 0,
            'paroquia_id' => $this->paroquia1->id,
            'is_pass_change' => 1,
            'user_consent' => 1,
            'accepted_photo' => 1,
        ]);
    }

    protected function createSchema(): void
    {
        Schema::create('paroquias_superadmin', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('slug', 255)->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('user', 100);
            $table->string('email', 100);
            $table->string('password', 255)->nullable();
            $table->string('rule', 55)->nullable();
            $table->integer('status')->default(0);
            $table->tinyInteger('is_visible')->default(1);
            $table->tinyInteger('hide_name')->default(0);
            $table->integer('is_pass_change')->default(0);
            $table->integer('login_attempts')->default(0);
            $table->timestamp('last_attempt')->nullable();
            $table->string('avatar', 20)->default('0');
            $table->tinyInteger('accepted_photo')->default(0);
            $table->tinyInteger('user_consent')->default(0);
            $table->integer('paroquia_id')->default(0);
            $table->date('created_at')->nullable();
            $table->string('timezone', 64)->nullable();
            $table->string('remember_token', 255)->nullable();
        });

        // Tabela de Logs de Atividades conforme a Migration criada
        $migration = require database_path('migrations/2026_10_27_150000_create_activity_logs_table.php');
        $migration->up();

        // Tabela simplificada de Notas Fiscais para testes de Model Auditing
        Schema::create('notas_fiscais', function (Blueprint $table) {
            $table->id();
            $table->integer('paroquia_id')->nullable();
            $table->integer('entidade_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('numero', 50)->nullable();
            $table->string('serie', 20)->nullable();
            $table->string('chave_acesso', 100)->nullable();
            $table->string('tipo', 20)->nullable();
            $table->date('data_emissao')->nullable();
            $table->date('data_entrada')->nullable();
            $table->string('emitente_nome', 150)->nullable();
            $table->string('emitente_documento', 50)->nullable();
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->decimal('valor_desconto', 10, 2)->default(0);
            $table->decimal('valor_acrescimo', 10, 2)->default(0);
            $table->text('descricao')->nullable();
            $table->string('caminho_arquivo', 255)->nullable();
            $table->string('status', 30)->default('emitida');
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabela simplificada de Ofertas para testes de integridade transacional atômica
        Schema::create('ofertas', function (Blueprint $table) {
            $table->id();
            $table->date('data')->nullable();
            $table->time('horario')->nullable();
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->string('tipo', 255)->nullable();
            $table->integer('kind')->default(1);
            $table->text('observacoes')->nullable();
            $table->integer('ent_id')->nullable();
            $table->integer('paroquia_id')->nullable();
            $table->timestamp('criado_em')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        // Tabela de módulos fixados para suporte ao View Composer do layouts.app
        Schema::create('pinned_modules', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('paroquia_id')->nullable();
            $table->string('module_slug', 100);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Tabela de lembretes para suporte às notificações do cabeçalho de layouts.app
        Schema::create('lembretes', function (Blueprint $table) {
            $table->id();
            $table->integer('usuario_id');
            $table->text('descricao')->nullable();
            $table->dateTime('data_hora')->nullable();
            $table->string('status', 30)->default('active');
            $table->string('repeat', 20)->nullable();
            $table->boolean('pref_email')->default(false);
            $table->boolean('pref_sound')->default(false);
            $table->dateTime('last_email_sent')->nullable();
            $table->dateTime('snooze_until')->nullable();
            $table->timestamps();
        });

        // Tabelas de mensagens e notificações consultadas pelo layouts.app
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->integer('sender_id')->nullable();
            $table->integer('receiver_id')->nullable();
            $table->text('content')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('protocol_status_notifications', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('protocol_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('processo_notificacoes', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('processo_id')->nullable();
            $table->string('titulo', 255)->nullable();
            $table->text('mensagem')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('batismos', function (Blueprint $table) {
            $table->id();
            $table->integer('register_id')->nullable();
            $table->integer('paroquia_id')->nullable();
            $table->boolean('is_batizado')->default(false);
            $table->date('data_batismo')->nullable();
            $table->string('local_batismo', 255)->nullable();
            $table->string('celebrante', 255)->nullable();
            $table->string('padrinho_nome', 255)->nullable();
            $table->string('madrinha_nome', 255)->nullable();
            $table->string('livro', 50)->nullable();
            $table->string('folha', 50)->nullable();
            $table->string('registro', 50)->nullable();
            $table->text('obs')->nullable();
            $table->timestamps();
        });
    }

    // ── 1. TESTES DE AUTORIZAÇÃO E CONTROLE DE ACESSO ────────────────────────

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/activity-logs');
        $response->assertRedirect('/login');
    }

    public function test_unauthorized_role_receives_forbidden(): void
    {
        $response = $this->actingAs($this->usuarioComum)->get('/activity-logs');
        // O SisMatriz protege as rotas no CheckModuleAccess retornando 404 para ocultar o módulo de quem não tem permissão
        $this->assertTrue(in_array($response->status(), [403, 404]), "Status retornado deve ser 403 ou 404, recebido: {$response->status()}");
    }

    public function test_role_1_parish_admin_can_access_own_parish_logs(): void
    {
        $response = $this->actingAs($this->adminParoquia1)->get('/activity-logs');
        $response->assertStatus(200);
        $response->assertSee('Logs de Atividades');
    }

    public function test_role_111_superadmin_can_access_activity_logs(): void
    {
        $response = $this->actingAs($this->adminGlobal111)->get('/activity-logs');
        $response->assertStatus(200);
        $response->assertSee('Logs de Atividades');
    }

    public function test_role_1_cannot_see_logs_of_other_parishes(): void
    {
        // Cria log da Paróquia 1
        ActivityLog::create([
            'paroquia_id' => $this->paroquia1->id,
            'user_id' => $this->adminParoquia1->id,
            'user_name' => $this->adminParoquia1->name,
            'category' => 'action',
            'action' => 'create',
            'module' => 'Notas Fiscais',
            'description' => 'Log Exclusivo Paróquia 1',
            'status' => 'success',
            'created_at' => now(),
        ]);

        // Cria log da Paróquia 2
        ActivityLog::create([
            'paroquia_id' => $this->paroquia2->id,
            'user_id' => 999,
            'user_name' => 'Outro Usuário',
            'category' => 'action',
            'action' => 'create',
            'module' => 'Notas Fiscais',
            'description' => 'Log Exclusivo Paróquia 2',
            'status' => 'success',
            'created_at' => now(),
        ]);

        // Admin da Paróquia 1 tenta consultar forçando parâmetro paroquia_id=2
        $response = $this->actingAs($this->adminParoquia1)->get('/activity-logs?paroquia_id=2');
        $response->assertStatus(200);

        // Deve visualizar apenas o log de sua paróquia
        $response->assertSee('Log Exclusivo Paróquia 1');
        $response->assertDontSee('Log Exclusivo Paróquia 2');
    }

    public function test_role_111_is_also_scoped_to_own_parish_on_listing(): void
    {
        ActivityLog::create([
            'paroquia_id' => $this->paroquia2->id,
            'user_id' => 999,
            'user_name' => 'Outro Usuário',
            'category' => 'action',
            'action' => 'create',
            'module' => 'Notas Fiscais',
            'description' => 'Log Paróquia 2 Inacessível',
            'status' => 'success',
            'created_at' => now(),
        ]);

        ActivityLog::create([
            'paroquia_id' => $this->adminGlobal111->paroquia_id,
            'user_id' => $this->adminGlobal111->id,
            'user_name' => $this->adminGlobal111->name,
            'category' => 'action',
            'action' => 'create',
            'module' => 'Notas Fiscais',
            'description' => 'Log Paróquia Própria do Admin 111',
            'status' => 'success',
            'created_at' => now(),
        ]);

        // Mesmo que passe paroquia_id=all na query string, a listagem escopa na paróquia do usuário
        $response = $this->actingAs($this->adminGlobal111)->get('/activity-logs?paroquia_id=all');
        $response->assertStatus(200);
        $response->assertSee('Log Paróquia Própria do Admin 111');
        $response->assertDontSee('Log Paróquia 2 Inacessível');
    }

    public function test_events_without_parish_not_exposed_to_parish_admin(): void
    {
        // Evento órfão (ex: tentativa de invasor desconhecido)
        ActivityLog::create([
            'paroquia_id' => null,
            'user_id' => null,
            'user_name' => 'invasor_anonimo',
            'category' => 'auth',
            'action' => 'login_failed',
            'module' => 'Autenticação',
            'description' => 'Ataque de força bruta externo',
            'status' => 'failed',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->adminParoquia1)->get('/activity-logs');
        $response->assertStatus(200);
        $response->assertDontSee('Ataque de força bruta externo');
    }

    public function test_no_edit_or_destroy_endpoints_exist_for_immutability(): void
    {
        $log = ActivityLog::create([
            'paroquia_id' => $this->paroquia1->id,
            'category' => 'action',
            'action' => 'test',
            'module' => 'Teste',
            'description' => 'Log Imutável',
            'status' => 'success',
            'created_at' => now(),
        ]);

        // Tentar DELETE deve retornar 404/405 (método não permitido/rota inexistente)
        $responseDelete = $this->actingAs($this->adminGlobal111)->delete("/activity-logs/{$log->id}");
        $this->assertTrue(in_array($responseDelete->status(), [404, 405]));

        // Tentar PUT deve retornar 404/405
        $responsePut = $this->actingAs($this->adminGlobal111)->put("/activity-logs/{$log->id}", [
            'description' => 'Tentativa de adulteração',
        ]);
        $this->assertTrue(in_array($responsePut->status(), [404, 405]));
    }

    // ── 2. TESTES DE AUDITORIA DE AUTENTICAÇÃO ────────────────────────────────

    public function test_successful_login_event_creates_audit_log(): void
    {
        Event::dispatch(new Login('web', $this->adminParoquia1, false));

        $this->assertDatabaseHas('activity_logs', [
            'category' => 'auth',
            'action' => 'login',
            'user_id' => $this->adminParoquia1->id,
            'paroquia_id' => $this->paroquia1->id,
            'status' => 'success',
        ]);
    }

    public function test_successful_logout_event_creates_audit_log(): void
    {
        Event::dispatch(new Logout('web', $this->adminParoquia1));

        $this->assertDatabaseHas('activity_logs', [
            'category' => 'auth',
            'action' => 'logout',
            'user_id' => $this->adminParoquia1->id,
            'paroquia_id' => $this->paroquia1->id,
            'status' => 'success',
        ]);
    }

    public function test_failed_login_does_not_leak_passwords(): void
    {
        $credentials = [
            'user' => 'admin_paroquia_1',
            'password' => 'SENHA_MUITO_SECRETA_123!@#',
        ];

        Event::dispatch(new Failed('web', $this->adminParoquia1, $credentials));

        // Deve registrar a falha de login
        $this->assertDatabaseHas('activity_logs', [
            'category' => 'auth',
            'action' => 'login_failed',
            'user_id' => $this->adminParoquia1->id,
            'status' => 'failed',
        ]);

        // A senha JAMAIS deve constar em qualquer coluna da tabela de logs
        $log = ActivityLog::where('action', 'login_failed')->first();
        $this->assertNotNull($log);
        $this->assertStringNotContainsString('SENHA_MUITO_SECRETA_123!@#', $log->description);
        $this->assertNull($log->old_values);
        $this->assertNull($log->new_values);
    }

    public function test_failed_login_for_nonexistent_user_has_null_parish(): void
    {
        $credentials = [
            'user' => 'usuario_fantasma_inexistente',
            'password' => 'qualquer',
        ];

        Event::dispatch(new Failed('web', null, $credentials));

        $this->assertDatabaseHas('activity_logs', [
            'category' => 'auth',
            'action' => 'login_failed',
            'user_id' => null,
            'paroquia_id' => null,
            'user_name' => 'usuario_fantasma_inexistente',
            'status' => 'failed',
        ]);
    }

    // ── 3. TESTES DE AUDITORIA DE MODELS E OPERAÇÕES CRUD ─────────────────────

    public function test_model_creation_creates_audit_log_with_allowlist(): void
    {
        $this->actingAs($this->adminParoquia1);

        $nota = NotaFiscal::create([
            'paroquia_id' => $this->paroquia1->id,
            'numero' => 'NF-9988',
            'serie' => '1',
            'valor_total' => 1500.50,
            'emitente_nome' => 'Fornecedor Teste LTDA',
            'status' => 'emitida',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'category' => 'model',
            'action' => 'create',
            'auditable_type' => NotaFiscal::class,
            'auditable_id' => $nota->id,
            'paroquia_id' => $this->paroquia1->id,
        ]);

        $log = ActivityLog::where('auditable_type', NotaFiscal::class)->where('action', 'create')->first();
        $this->assertEquals('NF-9988', $log->new_values['numero']);
        $this->assertEquals(1500.50, $log->new_values['valor_total']);
    }

    public function test_model_update_captures_diff_of_whitelisted_fields(): void
    {
        $this->actingAs($this->adminParoquia1);

        $nota = NotaFiscal::create([
            'paroquia_id' => $this->paroquia1->id,
            'numero' => 'NF-100',
            'valor_total' => 500.00,
            'status' => 'emitida',
        ]);

        // Atualiza valor e status
        $nota->valor_total = 750.00;
        $nota->status = 'paga';
        $nota->save();

        $updateLog = ActivityLog::where('auditable_type', NotaFiscal::class)
            ->where('action', 'update')
            ->first();

        $this->assertNotNull($updateLog);
        $this->assertEquals(500.00, $updateLog->old_values['valor_total']);
        $this->assertEquals(750.00, $updateLog->new_values['valor_total']);
        $this->assertEquals('emitida', $updateLog->old_values['status']);
        $this->assertEquals('paga', $updateLog->new_values['status']);
    }

    public function test_technical_fields_on_user_do_not_generate_audit_logs(): void
    {
        $this->actingAs($this->adminParoquia1);

        // Atualiza campo técnico recorrente que NÃO está na lista branca
        $this->adminParoquia1->login_attempts = 5;
        $this->adminParoquia1->remember_token = 'novo_token_sessao';
        $this->adminParoquia1->save();

        // Não deve gerar log de alteração de registro pois os campos técnicos foram ignorados
        $this->assertDatabaseMissing('activity_logs', [
            'auditable_type' => User::class,
            'action' => 'update',
        ]);
    }

    public function test_user_administrative_update_generates_audit_log(): void
    {
        $this->actingAs($this->adminParoquia1);

        $this->usuarioComum->rule = '2,4';
        $this->usuarioComum->status = 1; // Inativo
        $this->usuarioComum->save();

        $this->assertDatabaseHas('activity_logs', [
            'auditable_type' => User::class,
            'action' => 'update',
            'auditable_id' => $this->usuarioComum->id,
        ]);

        $log = ActivityLog::where('auditable_type', User::class)->where('action', 'update')->first();
        $this->assertEquals('4', $log->old_values['rule']);
        $this->assertEquals('2,4', $log->new_values['rule']);
        $this->assertEquals(0, $log->old_values['status']);
        $this->assertEquals(1, $log->new_values['status']);
    }

    public function test_model_deletion_records_audit_log(): void
    {
        $this->actingAs($this->adminParoquia1);

        $nota = NotaFiscal::create([
            'paroquia_id' => $this->paroquia1->id,
            'numero' => 'NF-EXCLUIR',
            'valor_total' => 120.00,
        ]);

        $nota->delete();

        $this->assertDatabaseHas('activity_logs', [
            'category' => 'model',
            'action' => 'delete',
            'auditable_type' => NotaFiscal::class,
            'auditable_id' => $nota->id,
        ]);

        $log = ActivityLog::where('auditable_type', NotaFiscal::class)->where('action', 'delete')->first();
        $this->assertEquals('NF-EXCLUIR', $log->old_values['numero']);
    }

    public function test_transaction_rollback_does_not_persist_success_log(): void
    {
        $this->actingAs($this->adminParoquia1);
        $logger = app(ActivityLoggerService::class);

        try {
            DB::beginTransaction();

            $logger->log([
                'category' => 'model',
                'action' => 'create',
                'module' => 'Financeiro',
                'description' => 'Operação que falhou no final',
                'paroquia_id' => $this->paroquia1->id,
            ], true); // Modo transacional estrito

            // Simula falha catastrófica
            throw new \Exception('Erro forçado de negócio');

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
        }

        // Como houve rollback, o log pós-commit NUNCA deve ser gravado
        $this->assertDatabaseMissing('activity_logs', [
            'description' => 'Operação que falhou no final',
        ]);
    }

    // ── 4. TESTES DE NAVEGAÇÃO E POLICIAMENTO DE RUÍDOS ──────────────────────

    public function test_navigation_middleware_skips_polling_routes(): void
    {
        $this->actingAs($this->adminParoquia1);

        // Simula request para rota ignorada de polling (dashboard.ping)
        $response = $this->post('/dashboard/ping');

        // Não deve ter sido gravado log de navegação
        $this->assertDatabaseMissing('activity_logs', [
            'category' => 'navigation',
            'url' => url('/dashboard/ping'),
        ]);
    }

    // ── 5. TESTES DO ENDPOINT DE INTERAÇÃO DO FRONTEND ────────────────────────

    public function test_frontend_interaction_endpoint_accepts_whitelisted_action(): void
    {
        $this->actingAs($this->adminParoquia1);

        $response = $this->postJson('/activity-logs/interaction', [
            'action' => 'modal_aberto',
            'module' => 'Notas Fiscais',
            'description' => 'Abriu modal de importação XML',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('activity_logs', [
            'category' => 'action',
            'action' => 'modal_aberto',
            'module' => 'Notas Fiscais',
            'description' => 'Abriu modal de importação XML',
            'source' => 'frontend',
            'user_id' => $this->adminParoquia1->id,
        ]);
    }

    public function test_frontend_interaction_endpoint_rejects_arbitrary_actions(): void
    {
        $this->actingAs($this->adminParoquia1);

        $response = $this->postJson('/activity-logs/interaction', [
            'action' => 'transferencia_dinheiro_falsa',
            'module' => 'Hacker',
            'description' => 'Tentativa de forjar ação indevida',
        ]);

        $response->assertStatus(422);
    }

    // ── 6. TESTE DO COMANDO ARTISAN DE MANUTENÇÃO (DRY-RUN) ──────────────────

    public function test_prune_activity_logs_command_with_dry_run(): void
    {
        // Cria log antigo (> 100 dias)
        ActivityLog::create([
            'paroquia_id' => $this->paroquia1->id,
            'category' => 'navigation',
            'action' => 'page_view',
            'module' => 'Dashboard',
            'description' => 'Visita antiga',
            'status' => 'success',
            'created_at' => now()->subDays(100),
        ]);

        // Executa com --dry-run
        $this->artisan('activity-logs:prune', [
            '--days' => 90,
            '--category' => 'navigation',
            '--dry-run' => true,
        ])
        ->expectsOutputToContain('[MODO SIMULAÇÃO ATIVO] Nenhum registro foi excluído do banco de dados.')
        ->assertExitCode(0);

        // O registro ainda deve existir intacto
        $this->assertDatabaseHas('activity_logs', [
            'description' => 'Visita antiga',
        ]);
    }

    // ── 7. TESTES DE AUDITORIA AVANÇADA, SHOW E INTERAÇÃO MULTI-TENANT ───────

    public function test_authenticated_common_user_can_record_interaction(): void
    {
        // Usuário comum (role 4) deve conseguir registrar interação autorizada de interface
        $response = $this->actingAs($this->usuarioComum)->postJson('/activity-logs/interaction', [
            'action'      => 'modal_aberto',
            'module'      => 'Acessos e Usuários',
            'description' => 'Usuário comum abriu visualizador',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('activity_logs', [
            'action'      => 'modal_aberto',
            'module'      => 'Acessos e Usuários',
            'user_id'     => $this->usuarioComum->id,
            'source'      => 'frontend',
        ]);
    }

    public function test_unauthenticated_user_cannot_record_interaction(): void
    {
        Auth::logout();

        $response = $this->postJson('/activity-logs/interaction', [
            'action'      => 'modal_aberto',
            'module'      => 'Acessos e Usuários',
            'description' => 'Acesso anônimo forjado',
        ]);

        $response->assertStatus(401);
    }

    public function test_show_endpoint_strictly_isolates_parishes(): void
    {
        // Cria um log pertencente à Paróquia 2
        $logParoquia2 = ActivityLog::create([
            'paroquia_id'    => $this->paroquia2->id,
            'user_id'        => 999,
            'category'       => 'model',
            'action'         => 'create',
            'module'         => 'Notas Fiscais',
            'description'    => 'Nota fiscal da paróquia 2',
            'status'         => 'success',
            'created_at'     => now(),
        ]);

        // Admin da Paróquia 1 tenta visualizar o log da Paróquia 2 por ID -> deve receber 404
        $response = $this->actingAs($this->adminParoquia1)->getJson("/activity-logs/{$logParoquia2->id}");
        $response->assertStatus(404);

        // Superadmin 111 pode visualizar qualquer paróquia -> deve receber 200
        $responseGlobal = $this->actingAs($this->adminGlobal111)->getJson("/activity-logs/{$logParoquia2->id}");
        $responseGlobal->assertStatus(200);
        $responseGlobal->assertJsonPath('description', 'Nota fiscal da paróquia 2');
    }

    public function test_show_endpoint_does_not_leak_sensitive_attributes(): void
    {
        $logSensivel = ActivityLog::create([
            'paroquia_id'    => $this->paroquia1->id,
            'user_id'        => $this->adminParoquia1->id,
            'category'       => 'model',
            'action'         => 'update',
            'module'         => 'Acessos e Usuários',
            'description'    => 'Alteração com tentativa de dados sensíveis',
            'old_values'     => ['name' => 'Nome Antigo', 'password' => 'segredo123', 'remember_token' => 'token123'],
            'new_values'     => ['name' => 'Nome Novo', 'password' => 'novasenha456', 'remember_token' => 'token456'],
            'status'         => 'success',
            'created_at'     => now(),
        ]);

        $response = $this->actingAs($this->adminParoquia1)->getJson("/activity-logs/{$logSensivel->id}");
        $response->assertStatus(200);

        // Garante que senhas e tokens não aparecem no JSON
        $json = $response->getContent();
        $this->assertStringNotContainsString('segredo123', $json);
        $this->assertStringNotContainsString('novasenha456', $json);
        $this->assertStringNotContainsString('token123', $json);
        $this->assertStringNotContainsString('token456', $json);
        $this->assertStringContainsString('Nome Novo', $json);
    }

    public function test_newly_audited_model_batismo_logs_properly(): void
    {
        Auth::login($this->adminParoquia1);

        $batismo = \App\Models\Batismo::create([
            'register_id'   => 10,
            'paroquia_id'   => $this->paroquia1->id,
            'is_batizado'   => true,
            'data_batismo'  => now()->toDateString(),
            'celebrante'    => 'Pe. Teste',
            'livro'         => 'Livro 01',
            'folha'         => '12',
            'registro'      => '34',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'category'       => 'model',
            'action'         => 'create',
            'module'         => 'Sacramentos - Batismo',
            'auditable_type' => \App\Models\Batismo::class,
            'auditable_id'   => $batismo->id,
            'paroquia_id'    => $this->paroquia1->id,
        ]);
    }

    public function test_atomic_transaction_rolls_back_log_on_exception(): void
    {
        Auth::login($this->adminParoquia1);

        try {
            DB::transaction(function () {
                app(ActivityLoggerService::class)->log([
                    'category'    => 'model',
                    'action'      => 'create',
                    'module'      => 'Financeiro Crítico',
                    'description' => 'Operação de alta criticidade',
                    'paroquia_id' => $this->paroquia1->id,
                ], 'atomic');

                // Lança exceção para forçar rollback da transação inteira
                throw new \Exception('Falha intencional na transação financeira');
            });
        } catch (\Exception $e) {
            // Esperado
        }

        // Como foi no modo 'atomic' e a transação deu rollback, o log também foi desfeito pelo banco
        $this->assertDatabaseMissing('activity_logs', [
            'description' => 'Operação de alta criticidade',
        ]);
    }

    public function test_permission_change_rolls_back_if_atomic_audit_fails(): void
    {
        Auth::login($this->adminParoquia1);

        // Usuário alvo inicialmente com regra '4' (usuário comum)
        $alvo = User::create([
            'name'           => 'Usuário Teste Permissão',
            'user'           => 'user_alvo_role',
            'email'          => 'alvo@sismatriz.test',
            'rule'           => '4',
            'status'         => 0,
            'paroquia_id'    => $this->paroquia1->id,
            'is_pass_change' => 1,
            'user_consent'   => 1,
            'accepted_photo' => 1,
        ]);

        $this->assertEquals('4', $alvo->fresh()->rule);

        // Mock parcial do ActivityLoggerService para simular falha catastrófica ao persistir o log atômico
        $mockService = $this->partialMock(ActivityLoggerService::class, function ($mock) {
            $mock->shouldReceive('log')
                ->withArgs(function ($data, $mode) {
                    return $mode === 'atomic';
                })
                ->andThrow(new \App\Exceptions\AuditLogPersistenceException('Falha de I/O de auditoria atômica'));
        });

        // Tentativa de promoção para Administrador Geral ('1')
        try {
            DB::transaction(function () use ($alvo) {
                $alvo->rule = '1'; // Tentativa de promoção crítica
                $alvo->save();
            });
            $this->fail('Deveria ter lançado AuditLogPersistenceException');
        } catch (\App\Exceptions\AuditLogPersistenceException $e) {
            // Exceção esperada da falha atômica
        }

        // COMPROVAÇÃO: O banco de dados reverteu e a permissão '1' NÃO foi aplicada!
        $this->assertEquals('4', $alvo->fresh()->rule, 'A permissão do usuário não deve mudar se a auditoria atômica falhar!');
    }

    public function test_financial_transaction_rolls_back_if_atomic_audit_fails(): void
    {
        Auth::login($this->adminParoquia1);

        // Mock parcial para simular falha no log atômico financeiro
        $this->partialMock(ActivityLoggerService::class, function ($mock) {
            $mock->shouldReceive('log')
                ->withArgs(function ($data, $mode) {
                    return $mode === 'atomic';
                })
                ->andThrow(new \App\Exceptions\AuditLogPersistenceException('Falha ao auditar lançamento financeiro'));
        });

        // Tentativa de lançamento financeiro de oferta de R$ 5.000,00
        try {
            DB::transaction(function () {
                \App\Models\Oferta::create([
                    'data'        => now()->toDateString(),
                    'valor_total' => 5000.00,
                    'kind'        => 1,
                    'paroquia_id' => $this->paroquia1->id,
                ]);
            });
            $this->fail('Deveria ter lançado AuditLogPersistenceException');
        } catch (\App\Exceptions\AuditLogPersistenceException $e) {
            // Exceção esperada
        }

        // COMPROVAÇÃO: O lançamento financeiro foi desfeito pelo rollback!
        $this->assertDatabaseMissing('ofertas', [
            'valor_total' => 5000.00,
        ]);
    }
}
