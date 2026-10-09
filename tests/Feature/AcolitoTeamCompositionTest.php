<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Acolito;
use App\Models\Paroquia;
use App\Models\AcolitoRegraComposicao;
use App\Services\AcolitoTeamCompositionService;

class AcolitoTeamCompositionTest extends TestCase
{
    use DatabaseTransactions;

    protected AcolitoTeamCompositionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Partial mock to bypass fetchRegras DB query which hits missing table
        $this->service = \Mockery::mock(AcolitoTeamCompositionService::class)->makePartial()->shouldAllowMockingProtectedMethods();
    }

    protected function mockRegras(array $regrasData)
    {
        $collection = collect();
        foreach ($regrasData as $data) {
            $regra = new \App\Models\AcolitoRegraComposicao();
            $regra->forceFill($data);
            $collection->push($regra);
        }

        $this->service->shouldReceive('fetchRegras')->andReturn($collection);
    }

    public function test_incompatibility_rule()
    {
        $paroquiaId = 999;
        $entId = 999;
        $baseData = ['type' => 0, 'status' => 0, 'paroquia_id' => $paroquiaId, 'ent_id' => $entId, 'age' => 15, 'graduation_year' => 2024];
        
        $acolito1Id = \Illuminate\Support\Facades\DB::table('acolitos')->insertGetId(array_merge(['name' => 'Acolito 1'], $baseData));
        $acolito2Id = \Illuminate\Support\Facades\DB::table('acolitos')->insertGetId(array_merge(['name' => 'Acolito 2'], $baseData));

        $this->mockRegras([
            ['tipo_regra' => 'incompatibilidade', 'acolito_id' => $acolito1Id, 'configuracao' => ['acolitos_incompativeis' => [$acolito2Id]]]
        ]);

        $erros = $this->service->validarEquipe($paroquiaId, [$acolito1Id, $acolito2Id]);
        $this->assertNotEmpty($erros);
        $this->assertStringContainsString('Acolito 1 não pode servir na mesma equipe que Acolito 2', $erros[0]);

        $errosValid = $this->service->validarEquipe($paroquiaId, [$acolito1Id]);
        $this->assertEmpty($errosValid);
    }

    public function test_mandatory_company_rule()
    {
        $paroquiaId = 999;
        $entId = 999;
        $baseData = ['type' => 0, 'status' => 0, 'paroquia_id' => $paroquiaId, 'ent_id' => $entId, 'age' => 15, 'graduation_year' => 2024];
        
        $novatoId = \Illuminate\Support\Facades\DB::table('acolitos')->insertGetId(array_merge(['name' => 'Novato'], $baseData));
        $experienteId = \Illuminate\Support\Facades\DB::table('acolitos')->insertGetId(array_merge(['name' => 'Experiente'], $baseData));

        $this->mockRegras([
            ['tipo_regra' => 'companhia_obrigatoria', 'acolito_id' => $novatoId, 'configuracao' => ['acolitos_obrigatorios' => [$experienteId], 'modo' => 'qualquer']]
        ]);

        $erros = $this->service->validarEquipe($paroquiaId, [$novatoId]);
        $this->assertNotEmpty($erros);
        $this->assertStringContainsString('Novato requer a presença de pelo menos um dos seguintes', $erros[0]);

        $errosValid = $this->service->validarEquipe($paroquiaId, [$novatoId, $experienteId]);
        $this->assertEmpty($errosValid);
    }

    public function test_minimum_accompaniment_rule()
    {
        $paroquiaId = 999;
        $entId = 999;
        $baseData = ['status' => 0, 'paroquia_id' => $paroquiaId, 'ent_id' => $entId, 'age' => 15, 'graduation_year' => 2024];
        
        $acolitoId = \Illuminate\Support\Facades\DB::table('acolitos')->insertGetId(array_merge(['name' => 'Acolito Minimo', 'type' => 0], $baseData));
        $acompanhanteId = \Illuminate\Support\Facades\DB::table('acolitos')->insertGetId(array_merge(['name' => 'Acompanhante', 'type' => 0], $baseData));
        $coroinhaId = \Illuminate\Support\Facades\DB::table('acolitos')->insertGetId(array_merge(['name' => 'Coroinha', 'type' => 1], $baseData));

        $this->mockRegras([
            ['tipo_regra' => 'acompanhamento_minimo', 'acolito_id' => $acolitoId, 'configuracao' => ['minimo_acompanhantes' => 1, 'categorias' => [0]]]
        ]);

        $erros1 = $this->service->validarEquipe($paroquiaId, [$acolitoId, $coroinhaId]);
        $this->assertNotEmpty($erros1);

        $erros2 = $this->service->validarEquipe($paroquiaId, [$acolitoId, $acompanhanteId]);
        $this->assertEmpty($erros2);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
