@extends('layouts.site-tools')

@section('tool-content')
<style>
    [x-cloak] { display: none !important; }
</style>

<div class="container-fluid px-0" style="max-width: 1400px;" x-cloak
     x-data="{
        secretaria: @js($secretariaHorarios),
        confissoes: @js($confissoesHorarios),
        adoracaoEnabled: @js($adoracaoEnabled),
        adoracao: @js($adoracaoHorarios),
        padronizarSecretariaDiasUteis() {
            const src = this.secretaria?.[1];
            const slot1 = src?.slots?.[0] ?? { start: '', end: '' };
            const slot2 = src?.slots?.[1] ?? { start: '', end: '' };

            for (let i = 1; i <= 6; i++) {
                const day = this.secretaria[i];
                if (!day) continue;
                day.closed = false;
                if (!Array.isArray(day.slots)) day.slots = [];
                if (!day.slots[0]) day.slots[0] = { start: '', end: '' };
                if (!day.slots[1]) day.slots[1] = { start: '', end: '' };
                day.slots[0].start = slot1.start ?? '';
                day.slots[0].end = slot1.end ?? '';
                day.slots[1].start = slot2.start ?? '';
                day.slots[1].end = slot2.end ?? '';
            }
        },
        addSlot(target, dayIndex) {
            this[target][dayIndex].slots.push({ start: '', end: '' });
        },
        removeSlot(target, dayIndex, slotIndex) {
            if (this[target][dayIndex].slots.length <= 1) return;
            this[target][dayIndex].slots.splice(slotIndex, 1);
        },
     }">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Ajustes da Paróquia</h2>
            <p class="text-muted mb-0">Defina horários da secretaria, confissões e adoração ao Santíssimo.</p>
        </div>
        <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm d-none d-sm-inline-block">Módulo Ativo</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
            <div class="fw-bold mb-2">Não foi possível salvar.</div>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('site-tools.paroquia-ajustes.save') }}">
        @csrf

        <input type="hidden" name="secretaria_horarios" :value="JSON.stringify(secretaria)">
        <input type="hidden" name="confissoes_horarios" :value="JSON.stringify(confissoes)">
        <input type="hidden" name="adoracao_enabled" :value="adoracaoEnabled ? 1 : 0">
        <input type="hidden" name="adoracao_horarios" :value="JSON.stringify(adoracao)">

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-clock-history me-2"></i>Secretaria (Atendimento)</h5>
                    <button type="button" class="btn btn-outline-primary rounded-pill fw-bold px-4"
                            @click="padronizarSecretariaDiasUteis()">
                        Padronizar dias úteis (Seg–Sáb)
                    </button>
                </div>
            </div>
            <div class="card-body p-4 p-md-5">
                <template x-for="(day, idx) in secretaria" :key="day.day">
                    <div class="card border-0 shadow-sm rounded-4 mb-3">
                        <div class="card-body p-4">
                            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                                <div class="fw-bold text-dark" x-text="day.label"></div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" x-model="day.closed" :id="`secretaria-closed-${idx}`">
                                    <label class="form-check-label text-muted" :for="`secretaria-closed-${idx}`">Fechado</label>
                                </div>
                            </div>

                            <div class="row g-3 mt-2" :class="day.closed ? 'opacity-50' : ''">
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label small text-muted mb-1">Horário 1 (Início)</label>
                                    <input type="time" class="form-control rounded-pill border-0 px-3 shadow-sm-hover"
                                           x-model="day.slots[0].start" :disabled="day.closed">
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label small text-muted mb-1">Horário 1 (Fim)</label>
                                    <input type="time" class="form-control rounded-pill border-0 px-3 shadow-sm-hover"
                                           x-model="day.slots[0].end" :disabled="day.closed">
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label small text-muted mb-1">Horário 2 (Início)</label>
                                    <input type="time" class="form-control rounded-pill border-0 px-3 shadow-sm-hover"
                                           x-model="day.slots[1].start" :disabled="day.closed">
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label small text-muted mb-1">Horário 2 (Fim)</label>
                                    <input type="time" class="form-control rounded-pill border-0 px-3 shadow-sm-hover"
                                           x-model="day.slots[1].end" :disabled="day.closed">
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-journal-text me-2"></i>Confissões</h5>
            </div>
            <div class="card-body p-4 p-md-5">
                <template x-for="(day, idx) in confissoes" :key="day.day">
                    <div class="card border-0 shadow-sm rounded-4 mb-3">
                        <div class="card-body p-4">
                            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                                <div class="fw-bold text-dark" x-text="day.label"></div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" x-model="day.enabled" :id="`confissoes-enabled-${idx}`">
                                    <label class="form-check-label text-muted" :for="`confissoes-enabled-${idx}`">Ativo</label>
                                </div>
                            </div>

                            <div class="mt-3" x-show="day.enabled" style="display: none;">
                                <template x-for="(slot, slotIdx) in day.slots" :key="`${day.day}-${slotIdx}`">
                                    <div class="row g-3 align-items-end mb-2">
                                        <div class="col-12 col-md-5">
                                            <label class="form-label small text-muted mb-1">Início</label>
                                            <input type="time" class="form-control rounded-pill border-0 px-3 shadow-sm-hover" x-model="slot.start">
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <label class="form-label small text-muted mb-1">Fim</label>
                                            <input type="time" class="form-control rounded-pill border-0 px-3 shadow-sm-hover" x-model="slot.end">
                                        </div>
                                        <div class="col-12 col-md-2 d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary rounded-pill w-100"
                                                    @click="addSlot('confissoes', idx)">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger rounded-pill w-100"
                                                    @click="removeSlot('confissoes', idx, slotIdx)"
                                                    :disabled="day.slots.length <= 1">
                                                <i class="bi bi-dash-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-stars me-2"></i>Adoração ao Santíssimo</h5>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" x-model="adoracaoEnabled" id="adoracao-enabled">
                        <label class="form-check-label text-muted" for="adoracao-enabled">A paróquia tem adoração</label>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 p-md-5" x-show="adoracaoEnabled" style="display: none;">
                <template x-for="(day, idx) in adoracao" :key="day.day">
                    <div class="card border-0 shadow-sm rounded-4 mb-3">
                        <div class="card-body p-4">
                            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                                <div class="fw-bold text-dark" x-text="day.label"></div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" x-model="day.enabled" :id="`adoracao-day-enabled-${idx}`">
                                    <label class="form-check-label text-muted" :for="`adoracao-day-enabled-${idx}`">Ativo</label>
                                </div>
                            </div>

                            <div class="mt-3" x-show="day.enabled" style="display: none;">
                                <template x-for="(slot, slotIdx) in day.slots" :key="`${day.day}-${slotIdx}`">
                                    <div class="row g-3 align-items-end mb-2">
                                        <div class="col-12 col-md-5">
                                            <label class="form-label small text-muted mb-1">Início</label>
                                            <input type="time" class="form-control rounded-pill border-0 px-3 shadow-sm-hover" x-model="slot.start">
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <label class="form-label small text-muted mb-1">Fim</label>
                                            <input type="time" class="form-control rounded-pill border-0 px-3 shadow-sm-hover" x-model="slot.end">
                                        </div>
                                        <div class="col-12 col-md-2 d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary rounded-pill w-100"
                                                    @click="addSlot('adoracao', idx)">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger rounded-pill w-100"
                                                    @click="removeSlot('adoracao', idx, slotIdx)"
                                                    :disabled="day.slots.length <= 1">
                                                <i class="bi bi-dash-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                <i class="bi bi-save me-2"></i>Salvar Ajustes
            </button>
        </div>
    </form>
</div>
@endsection
