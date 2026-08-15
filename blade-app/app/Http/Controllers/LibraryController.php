<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Exceptions\ServiceUnavailableException;
use App\Helpers\RoleHelper;
use App\Services\LibraryCoreService;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function __construct(private LibraryCoreService $library) {}

    // ─── Books ───────────────────────────────────────────────────────────────

    public function books(Request $request): mixed
    {
        $filters = $request->only(['title', 'autor', 'category', 'available']);

        try {
            $data = $this->library->getBooks($filters);
        } catch (ServiceUnavailableException $e) {
            return view('errors.service')->with('message', 'Biblioteca no disponible en este momento.');
        }

        return view('library.books', [
            'books' => $data['data'],
            'meta'  => $data['meta'],
        ]);
    }

    public function book(string $id): mixed
    {
        try {
            $data = $this->library->getBook($id);
        } catch (ServiceUnavailableException $e) {
            return view('errors.service')->with('message', 'Biblioteca no disponible en este momento.');
        }

        return view('library.book', ['book' => $data[0] ?? null]);
    }

    // ─── Loans ───────────────────────────────────────────────────────────────

    public function requestLoan(string $bookId): mixed
    {
        try {
            $this->library->requestLoan($bookId);
        } catch (BusinessException $e) {
            return redirect()->route('library.books')
                ->withErrors(['error' => $e->getMessage()]);
        } catch (ServiceUnavailableException $e) {
            return redirect()->route('library.books')
                ->withErrors(['error' => 'No se pudo procesar el préstamo. Intenta más tarde.']);
        }

        return redirect()->route('library.loans')
            ->with('success', 'Préstamo solicitado correctamente.');
    }

    public function loans(): mixed
    {
        try {
            $data = $this->library->getMyLoans();
        } catch (ServiceUnavailableException $e) {
            return view('errors.service')->with('message', 'Biblioteca no disponible en este momento.');
        }

        return view('library.loans', ['loans' => $data['loans']]);
    }

    public function renewLoan(string $loanId): mixed
    {
        try {
            $this->library->renewLoan($loanId);
        } catch (BusinessException $e) {
            return redirect()->route('library.loans')
                ->withErrors(['error' => $e->getMessage()]);
        } catch (ServiceUnavailableException $e) {
            return redirect()->route('library.loans')
                ->withErrors(['error' => 'No se pudo renovar el préstamo. Intenta más tarde.']);
        }

        return redirect()->route('library.loans')
            ->with('success', 'Préstamo renovado correctamente.');
    }

    public function returnBook(string $loanId): mixed
    {
        try {
            $result = $this->library->returnBook($loanId);
        } catch (BusinessException $e) {
            return redirect()->route('library.loans')
                ->withErrors(['error' => $e->getMessage()]);
        } catch (ServiceUnavailableException $e) {
            return redirect()->route('library.loans')
                ->withErrors(['error' => 'No se pudo devolver el libro. Intenta más tarde.']);
        }

        $message = 'Libro devuelto correctamente.';
        if (!empty($result['fine_generated'])) {
            $message .= ' Se generó una multa de $' . number_format($result['fine_amount'], 2) . ' por retraso.';
        }

        return redirect()->route('library.loans')->with('success', $message);
    }

    // ─── Fines ───────────────────────────────────────────────────────────────

    public function fines(Request $request): mixed
    {
        $status = $request->query('status');

        try {
            $data = $this->library->getMyFines($status);
        } catch (ServiceUnavailableException $e) {
            return view('errors.service')->with('message', 'Biblioteca no disponible en este momento.');
        }

        return view('library.fines', [
            'fines' => $data['fines'],
            'meta'  => $data['meta'],
        ]);
    }

    public function payFine(string $fineId): mixed
    {
        try {
            $this->library->payFine($fineId);
        } catch (BusinessException $e) {
            return redirect()->route('library.fines')
                ->withErrors(['error' => $e->getMessage()]);
        } catch (ServiceUnavailableException $e) {
            return redirect()->route('library.fines')
                ->withErrors(['error' => 'No se pudo procesar el pago. Intenta más tarde.']);
        }

        return redirect()->route('library.fines')
            ->with('success', 'Multa pagada correctamente.');
    }


    // ─── Inventory (librarian) ───────────────────────────────────────────────────

    public function inventory(Request $request): mixed
    {
        if (!RoleHelper::isLibrarian() && !RoleHelper::hasAnyRole(['super_admin', 'academic_admin'])) {
            abort(403);
        }

        $filters = $request->only(['title', 'autor', 'category', 'available']);

        try {
            $data = $this->library->getBooks($filters);
        } catch (ServiceUnavailableException $e) {
            return view('errors.service')->with('message', 'Biblioteca no disponible en este momento.');
        }

        return view('library.inventory', [
            'books' => $data['data'],
            'meta'  => $data['meta'],
        ]);
    }

    public function createBook(Request $request): mixed
    {
        $validated = $request->validate([
            'title'       => ['required', 'string'],
            'author'      => ['required', 'string'],
            'isbn'        => ['required', 'string'],
            'category'    => ['required', 'string'],
            'stock_total' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $this->library->createBook($validated);
        } catch (BusinessException $e) {
            return redirect()->route('library.inventory')
                ->withErrors(['error' => $e->getMessage()]);
        } catch (ServiceUnavailableException $e) {
            return redirect()->route('library.inventory')
                ->withErrors(['error' => 'No se pudo agregar el libro. Intenta más tarde.']);
        }

        return redirect()->route('library.inventory')
            ->with('success', 'Libro agregado correctamente.');
    }

    public function updateBook(Request $request, string $id): mixed
    {
        $validated = $request->validate([
            'title'    => ['sometimes', 'string'],
            'author'   => ['sometimes', 'string'],
            'isbn'     => ['sometimes', 'string'],
            'category' => ['sometimes', 'string'],
        ]);

        try {
            $this->library->updateBook($id, $validated);
        } catch (BusinessException $e) {
            return redirect()->route('library.inventory')
                ->withErrors(['error' => $e->getMessage()]);
        } catch (ServiceUnavailableException $e) {
            return redirect()->route('library.inventory')
                ->withErrors(['error' => 'No se pudo actualizar el libro. Intenta más tarde.']);
        }

        return redirect()->route('library.inventory')
            ->with('success', 'Libro actualizado correctamente.');
    }

    public function updateStock(Request $request, string $id): mixed
    {
        $validated = $request->validate([
            'stock_total' => ['required', 'integer', 'not_in:0'],
        ]);

        try {
            $this->library->updateStock($id, $validated['stock_total']);
        } catch (BusinessException $e) {
            return redirect()->route('library.inventory')
                ->withErrors(['error' => $e->getMessage()]);
        } catch (ServiceUnavailableException $e) {
            return redirect()->route('library.inventory')
                ->withErrors(['error' => 'No se pudo actualizar el stock. Intenta más tarde.']);
        }

        return redirect()->route('library.inventory')
            ->with('success', 'Stock actualizado correctamente.');
    }
}
