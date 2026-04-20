<?php

namespace App\Modules\Commerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Commerce\Actions\Books\SaveBookAction;
use App\Modules\Commerce\Enums\BookAvailability;
use App\Modules\Commerce\Http\Requests\Admin\Books\StoreBookRequest;
use App\Modules\Commerce\Http\Requests\Admin\Books\UpdateBookRequest;
use App\Modules\Commerce\Models\Book;
use App\Modules\Commerce\Queries\BooksIndexQuery;
use App\Shared\Contracts\AuditLogger;
use App\Shared\Support\Exports\CsvExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookController extends Controller
{
    public function __construct(
        private readonly BooksIndexQuery $booksIndexQuery,
        private readonly SaveBookAction $saveBookAction,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function index(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', Book::class);

        $query = $this->booksIndexQuery->builder($request);

        if ($request->string('export')->toString() === 'csv') {
            return CsvExporter::download('books.csv', ['الكتاب', 'المؤلف', 'المخزون', 'الحالة'], $query->get()
                ->map(fn (Book $book): array => [
                    $book->product?->name_ar ?? '-',
                    $book->author_name ?? '-',
                    (string) $book->stock_quantity,
                    $book->availability_status->label(),
                ])
                ->all());
        }

        return view('admin.commerce.books.index', [
            'books' => $query->paginate(15)->withQueryString(),
            'overview' => [
                'total' => Book::query()->count(),
                'available' => Book::query()->where('availability_status', BookAvailability::InStock->value)->count(),
                'featured' => Book::query()->whereHas('product', fn ($builder) => $builder->where('is_featured', true))->count(),
                'stock' => (int) Book::query()->sum('stock_quantity'),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Book::class);

        return view('admin.commerce.books.create', [
            'book' => new Book(['availability_status' => BookAvailability::InStock]),
            'availabilities' => BookAvailability::cases(),
        ]);
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        $this->authorize('create', Book::class);

        $this->saveBookAction->execute($request->validated(), auth('admin')->user());

        return redirect()
            ->route('admin.books.index')
            ->with('status', 'تم إنشاء الكتاب.');
    }

    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        return view('admin.commerce.books.edit', [
            'book' => $book->load('product'),
            'availabilities' => BookAvailability::cases(),
        ]);
    }

    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $this->saveBookAction->execute($request->validated(), auth('admin')->user(), $book);

        return redirect()
            ->route('admin.books.index')
            ->with('status', 'تم تحديث الكتاب.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $oldValues = $book->toArray();
        $book->delete();

        $this->auditLogger->log(
            event: 'commerce.book.deleted',
            actor: auth('admin')->user(),
            subject: $book,
            oldValues: $oldValues,
        );

        return redirect()
            ->route('admin.books.index')
            ->with('status', 'تم حذف الكتاب.');
    }
}
