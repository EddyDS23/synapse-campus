<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Exceptions\ServiceUnavailableException;
use App\Helpers\RoleHelper;
use App\Services\SupportDeskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SupportController extends Controller
{
    public function __construct(private SupportDeskService $support) {}

    // ─── Student: mis tickets ─────────────────────────────────────────────────

    public function myTickets(Request $request): mixed
    {
        $status = $request->query('status');
        try {
            $data = $this->support->getMyTickets($status);
        } catch (ServiceUnavailableException $e) {
            return view('errors.service')->with('message', 'Mesa de ayuda no disponible en este momento.');
        }

        return view('support.my-tickets', [
            'tickets'  => $data['data'],
            'meta'     => $data['metadata'],
            'statuses' => ['open', 'in_progress', 'on_hold', 'resolved', 'closed'],
        ]);
    }

    // ─── Agent: todos los tickets ─────────────────────────────────────────────

    public function allTickets(Request $request): mixed
    {
        if (!RoleHelper::isAgent()) {
            return redirect()->route('support.my-tickets');
        }

        $filters = $request->only(['status', 'priority', 'category_id', 'assignee_id']);

        try {
            $data = $this->support->getAllTickets($filters);
        } catch (ServiceUnavailableException $e) {
            return view('errors.service')->with('message', 'Mesa de ayuda no disponible en este momento.');
        }

        return view('support.all-tickets', [
            'tickets'    => $data['data'],
            'meta'       => $data['metadata'],
            'statuses'   => ['open', 'in_progress', 'on_hold', 'resolved', 'closed'],
            'priorities' => ['low', 'medium', 'high', 'urgent'],
        ]);
    }

    // ─── Detalle de ticket ────────────────────────────────────────────────────

    public function ticket(string $id): mixed
    {
        try {
            $ticket = $this->support->getTicket($id);
        } catch (ServiceUnavailableException $e) {
            return view('errors.service')->with('message', 'Mesa de ayuda no disponible en este momento.');
        }

        return view('support.ticket', [
            'ticket'  => $ticket,
            'isAgent' => RoleHelper::isAgent(),
        ]);
    }

    // ─── Crear ticket ─────────────────────────────────────────────────────────

    public function createForm(): mixed
    {
        return view('support.create');
    }

    public function createTicket(Request $request): mixed
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority'    => ['sometimes', 'string', 'in:low,medium,high,urgent'],
            'category_id' => ['required', 'string'],
        ]);

        try {
            $this->support->createTicket($validated);
        } catch (ServiceUnavailableException $e) {
            return back()->withErrors(['error' => 'No se pudo crear el ticket. Intenta más tarde.'])->withInput();
        }

        return redirect()->route('support.my-tickets')
            ->with('success', 'Ticket creado correctamente.');
    }

    // ─── Comentar ─────────────────────────────────────────────────────────────

    public function comment(Request $request, string $id): mixed
    {
        $validated = $request->validate([
            'body'        => ['required', 'string'],
            'is_internal' => ['sometimes', 'boolean'],
        ]);

        $isInternal = RoleHelper::isAgent() && ($validated['is_internal'] ?? false);

        try {
            $this->support->addComment($id, $validated['body'], $isInternal);
        } catch (ServiceUnavailableException $e) {
            return back()->withErrors(['error' => 'No se pudo agregar el comentario.']);
        }

        return redirect()->route('support.ticket', $id)
            ->with('success', 'Comentario agregado.');
    }

    // ─── Asignar (agente) ─────────────────────────────────────────────────────

    public function assign(Request $request, string $id): mixed
    {
        $validated = $request->validate([
            'assignee_id' => ['required', 'string'],
        ]);

        try {
            $this->support->assignTicket($id, $validated['assignee_id']);
        } catch (ServiceUnavailableException $e) {
            return back()->withErrors(['error' => 'No se pudo asignar el ticket.']);
        }

        return redirect()->route('support.ticket', $id)
            ->with('success', 'Ticket asignado correctamente.');
    }

    // ─── Cambiar estado (agente) ──────────────────────────────────────────────

    public function updateStatus(Request $request, string $id): mixed
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
        ]);

        try {
            $this->support->updateStatus($id, $validated['status']);
        } catch (BusinessException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (ServiceUnavailableException $e) {
            return back()->withErrors(['error' => 'No se pudo actualizar el estado.']);
        }

        return redirect()->route('support.ticket', $id)
            ->with('success', 'Estado actualizado.');
    }

    // ─── Reabrir (solicitante) ────────────────────────────────────────────────

    public function reopen(string $id): mixed
    {
        try {
            $this->support->reopenTicket($id);
        } catch (BusinessException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (ServiceUnavailableException $e) {
            return back()->withErrors(['error' => 'No se pudo reabrir el ticket.']);
        }

        return redirect()->route('support.ticket', $id)
            ->with('success', 'Ticket reabierto.');
    }

    // ─── Security context (security_admin) ───────────────────────────────────

    public function securityContext(string $id): mixed
    {
        if (!RoleHelper::isSecurityAdmin()) {
            abort(403);
        }

        try {
            $data = $this->support->getSecurityContext($id);
        } catch (ServiceUnavailableException $e) {
            return back()->withErrors(['error' => 'No se pudo obtener el contexto de seguridad.']);
        }

        return view('support.security-context', [
            'ticket'          => $data['ticket'],
            'securityContext' => $data['security_context'],
        ]);
    }
}