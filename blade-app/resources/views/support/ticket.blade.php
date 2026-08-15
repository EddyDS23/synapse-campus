@extends('layouts.app')

@section('title', 'Ticket: ' . $ticket['title'])

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Chosen Palette: Synapse Midnight (Dark Slate, Indigo & Semantic Accents) -->
<!-- Application Structure Plan: 
    1. Navigation & Context: Breadcrumbs and a status-aware header that changes color based on ticket urgency.
    2. Ticket Dossier: A primary grid layout where the left side handles the description and conversation, and the right side manages metadata and agent actions.
    3. Interactive Timeline: A visual CSS/HTML progression bar showing the ticket's lifecycle stages.
    4. Communication Hub: Nested glassmorphism cards for comments, with distinct styling for internal agent-only notes.
    5. Agent Command Panel: A specialized UI section for status changes and assignments, visible only to authorized users.
-->

<!-- Visualization & Content Choices:
    - Lifecycle Progress Bar -> Goal: Change -> Visual representation of ticket stages (Open -> In Progress -> Resolved).
    - Status Badges -> Goal: Inform -> Semantic colors (Emerald for Resolved, Red for Urgent).
    - Chat-style Comments -> Goal: Organize -> Chronological flow of communication.
    - Glassmorphism Controls -> Goal: Action -> High-contrast forms for ticket management.
    - CONFIRMATION: NO SVG graphics used. NO Mermaid JS used.
-->

<style>
    :root {
        --bg: #020617;
        --card-bg: rgba(30, 41, 59, 0.4);
        --accent: #6366f1;
    }

    body {
        background-color: var(--bg);
        color: #f1f5f9;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .synapse-glass {
        background: var(--card-bg);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 1.5rem;
    }

    .status-pulse {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        position: relative;
    }

    .status-pulse::after {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        background: inherit;
        opacity: 0.4;
        animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse-ring {
        0% { transform: scale(0.8); opacity: 0.5; }
        100% { transform: scale(2); opacity: 0; }
    }

    .comment-internal {
        background: rgba(245, 158, 11, 0.05);
        border-left: 4px solid #f59e0b !important;
    }

    .input-field {
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid rgba(148, 163, 184, 0.2);
        transition: all 0.3s ease;
    }

    .input-field:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        outline: none;
    }

    .btn-action {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        transition: all 0.2s ease;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
    }
</style>

<div class="max-w-7xl mx-auto px-4 py-10">
    
    <!-- Breadcrumbs & Header -->
    <nav class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ $isAgent ? route('support.all-tickets') : route('support.my-tickets') }}" class="w-10 h-10 rounded-xl border border-slate-800 flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-800 transition-all">
                ←
            </a>
            <div>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">ID: #TK-{{ str_pad($ticket['id'], 5, '0', STR_PAD_LEFT) }}</p>
                <h1 class="text-3xl font-black text-white leading-tight">{{ $ticket['title'] }}</h1>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @php
                $statusColor = match(strtolower($ticket['status'])) {
                    'open', 'abierto' => 'bg-indigo-500',
                    'resolved', 'resuelto' => 'bg-emerald-500',
                    'closed', 'cerrado' => 'bg-slate-500',
                    'in_progress', 'en progreso' => 'bg-amber-500',
                    default => 'bg-red-500'
                };
            @endphp
            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-slate-900 border border-slate-800">
                <span class="status-pulse {{ $statusColor }}"></span>
                <span class="text-xs font-bold text-white uppercase tracking-tighter">{{ $ticket['status'] }}</span>
            </div>
            <div class="px-4 py-2 rounded-full bg-slate-900 border border-slate-800">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-tighter">Prioridad:</span>
                <span class="text-xs font-bold text-indigo-400 uppercase ml-1">{{ $ticket['priority'] }}</span>
            </div>
        </div>
    </nav>

    <!-- Feedback Alerts -->
    <div id="alert-container" class="mb-8">
        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm font-bold animate-pulse">
                ⚠️ {{ $errors->first('error') }}
            </div>
        @endif
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-bold">
                ✓ {{ session('success') }}
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Dossier & Conversation -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Description Card -->
            <section class="synapse-glass p-8">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-6">Detalles del Reporte</h3>
                <div class="text-slate-300 leading-relaxed text-lg bg-slate-950/40 p-6 rounded-2xl border border-slate-800">
                    {{ $ticket['description'] }}
                </div>
                
                <!-- Lifecycle Progress (Simplified HTML Visualization) -->
                <div class="mt-10 pt-10 border-t border-slate-800">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xs font-bold text-slate-500 uppercase">Ciclo de Resolución</span>
                        <span class="text-xs font-bold text-indigo-400">{{ $ticket['status'] === 'resolved' ? '100%' : ($ticket['status'] === 'in_progress' ? '50%' : '10%') }}</span>
                    </div>
                    <div class="h-2 w-full bg-slate-900 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-500 rounded-full transition-all duration-1000" style="width: {{ $ticket['status'] === 'resolved' || $ticket['status'] === 'closed' ? '100%' : ($ticket['status'] === 'in_progress' ? '50%' : '15%') }}"></div>
                    </div>
                </div>
            </section>

            <!-- Reopen Form for Users -->
            @if(!$isAgent && (strtolower($ticket['status']) === 'resolved' || strtolower($ticket['status']) === 'resuelto'))
                <div class="synapse-glass p-6 border-l-4 border-l-amber-500 flex items-center justify-between">
                    <div>
                        <h4 class="text-white font-bold">¿El problema persiste?</h4>
                        <p class="text-xs text-slate-400">Si la solución no fue efectiva, puedes reabrir el caso.</p>
                    </div>
                    <form method="POST" action="{{ route('support.reopen', $ticket['id']) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="bg-amber-600 hover:bg-amber-500 text-white px-6 py-2 rounded-xl text-xs font-bold transition-all">
                            Reabrir Ticket
                        </button>
                    </form>
                </div>
            @endif

            <!-- Comments/Timeline -->
            <section class="space-y-6">
                <h3 class="text-xl font-black text-white flex items-center gap-3">
                    <span class="w-10 h-10 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-sm">💬</span>
                    Actividad y Comentarios
                </h3>

                @if(empty($ticket['comments']))
                    <div class="synapse-glass p-12 text-center border-dashed">
                        <p class="text-slate-500 font-medium">No se han registrado comentarios en este hilo aún.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($ticket['comments'] as $comment)
                            <div class="synapse-glass p-6 {{ ($isAgent && isset($comment['is_internal']) && $comment['is_internal']) ? 'comment-internal' : '' }}">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center font-bold text-indigo-400 text-xs">
                                            {{ substr($comment['user_name'] ?? 'U', 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-white leading-none">{{ $comment['user_name'] ?? 'Usuario Synapse' }}</p>
                                            <p class="text-[10px] text-slate-500 font-bold uppercase mt-1">HACE MOMENTOS</p>
                                        </div>
                                    </div>
                                    @if($isAgent && isset($comment['is_internal']) && $comment['is_internal'])
                                        <span class="text-[9px] font-black uppercase text-amber-500 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">Solo Agentes</span>
                                    @endif
                                </div>
                                <div class="text-slate-300 text-sm leading-relaxed pl-11">
                                    {{ $comment['body'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Comment Input -->
                @if(strtolower($ticket['status']) !== 'closed' && strtolower($ticket['status']) !== 'cerrado')
                    <div class="synapse-glass p-6 mt-8">
                        <form method="POST" action="{{ route('support.comment', $ticket['id']) }}" class="space-y-4">
                            @csrf
                            <textarea name="body" rows="4" required class="input-field w-full px-5 py-4 rounded-xl text-white placeholder-slate-600 text-sm" placeholder="Escribe una respuesta o actualización..."></textarea>
                            
                            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                                @if($isAgent)
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="checkbox" name="is_internal" value="1" class="w-5 h-5 rounded border-slate-700 bg-slate-900 text-amber-500 focus:ring-amber-500">
                                        <span class="text-xs font-bold text-slate-500 group-hover:text-amber-500 transition-colors">Nota interna (Privado)</span>
                                    </label>
                                @else
                                    <div></div>
                                @endif
                                <button type="submit" class="btn-action w-full md:w-auto px-8 py-3 rounded-xl font-bold text-white text-sm">
                                    Enviar Comentario
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </section>
        </div>

        <!-- Right: Metadata & Agent Panel -->
        <div class="space-y-8">
            
            <!-- Metadata Card -->
            <div class="synapse-glass p-6 space-y-6">
                <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Cronología de Eventos</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="w-1 h-10 bg-slate-800 rounded-full"></div>
                        <div>
                            <p class="text-[10px] font-black text-slate-500 uppercase">Apertura</p>
                            <p class="text-sm font-bold text-white">{{ $ticket['created_at'] ?? 'Hoy' }}</p>
                        </div>
                    </div>
                    @if($ticket['resolved_at'])
                        <div class="flex items-center gap-4">
                            <div class="w-1 h-10 bg-emerald-500 rounded-full"></div>
                            <div>
                                <p class="text-[10px] font-black text-emerald-500 uppercase">Resolución</p>
                                <p class="text-sm font-bold text-white">{{ \Carbon\Carbon::parse($ticket['resolved_at'])->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endif
                    @if($ticket['closed_at'])
                        <div class="flex items-center gap-4">
                            <div class="w-1 h-10 bg-slate-500 rounded-full"></div>
                            <div>
                                <p class="text-[10px] font-black text-slate-500 uppercase">Cierre Definitivo</p>
                                <p class="text-sm font-bold text-white">{{ \Carbon\Carbon::parse($ticket['closed_at'])->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Agent Command Panel -->
            @if($isAgent)
                <div class="synapse-glass p-6 border-t-4 border-t-indigo-600">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="text-xl">🛠️</span>
                        <h3 class="text-sm font-black text-white uppercase tracking-tight">Panel de Control Agente</h3>
                    </div>

                    <div class="space-y-6">
                        <!-- Assignment Form -->
                        <form method="POST" action="{{ route('support.assign', $ticket['id']) }}" class="space-y-2">
                            @csrf
                            @method('PATCH')
                            <label class="text-[10px] font-bold text-slate-500 uppercase ml-1">Asignar Responsable</label>
                            <div class="flex gap-2">
                                <input type="text" name="assignee_id" placeholder="ULID del agente" required class="input-field flex-grow px-4 py-2 rounded-lg text-xs text-white">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg text-xs font-bold transition-all">Asignar</button>
                            </div>
                        </form>

                        <!-- Status Transition -->
                        <form method="POST" action="{{ route('support.status', $ticket['id']) }}" class="space-y-2">
                            @csrf
                            @method('PATCH')
                            <label class="text-[10px] font-bold text-slate-500 uppercase ml-1">Transición de Estado</label>
                            <div class="flex gap-2">
                                <select name="status" class="input-field flex-grow px-4 py-2 rounded-lg text-xs text-white cursor-pointer appearance-none">
                                    <option value="in_progress">En progreso</option>
                                    <option value="on_hold">En espera</option>
                                    <option value="resolved">Resuelto</option>
                                    <option value="closed">Cerrado</option>
                                </select>
                                <button type="submit" class="bg-slate-800 hover:bg-indigo-600 border border-slate-700 text-white px-4 py-2 rounded-lg text-xs font-bold transition-all">Actualizar</button>
                            </div>
                        </form>

                        <!-- Special Context (Security) -->
                        @if(\App\Helpers\RoleHelper::isSecurityAdmin())
                            <div class="pt-4 border-t border-slate-800">
                                <a href="{{ route('support.security-context', $ticket['id']) }}" class="flex items-center justify-between p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20 transition-all">
                                    <span class="text-[10px] font-black uppercase">Seguridad Avanzada</span>
                                    <span class="text-xs">→</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Help/Info Card -->
            <div class="synapse-glass p-6 bg-indigo-500/5">
                <h3 class="text-sm font-bold text-white mb-4">Preguntas Frecuentes</h3>
                <div class="space-y-4">
                    <details class="group">
                        <summary class="text-xs font-bold text-slate-400 cursor-pointer list-none flex justify-between items-center hover:text-white transition-colors">
                            ¿Cuándo se cierra el ticket?
                            <span class="group-open:rotate-180 transition-transform">↓</span>
                        </summary>
                        <p class="text-[11px] text-slate-500 mt-2 leading-relaxed">Los tickets se cierran automáticamente tras 48 horas de inactividad una vez marcados como "Resueltos".</p>
                    </details>
                    <details class="group">
                        <summary class="text-xs font-bold text-slate-400 cursor-pointer list-none flex justify-between items-center hover:text-white transition-colors">
                            ¿Puedo adjuntar archivos?
                            <span class="group-open:rotate-180 transition-transform">↓</span>
                        </summary>
                        <p class="text-[11px] text-slate-500 mt-2 leading-relaxed">Por seguridad, solo puedes enviar enlaces a repositorios o carpetas compartidas institucionales en los comentarios.</p>
                    </details>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Subtle animation for alerts if they exist
        const alerts = document.querySelectorAll('#alert-container > div');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                alert.style.transition = 'all 0.5s ease';
                setTimeout(() => alert.remove(), 500);
            }, 6000);
        });
    });
</script>

@endsection