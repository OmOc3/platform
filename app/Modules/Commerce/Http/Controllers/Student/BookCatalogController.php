<?php

namespace App\Modules\Commerce\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Commerce\Models\Book;
use App\Modules\Commerce\Models\CartItem;
use App\Modules\Commerce\Queries\BookCatalogQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BookCatalogController extends Controller
{
    public function __construct(private readonly BookCatalogQuery $bookCatalogQuery)
    {
    }

    public function index(Request $request): View
    {
        $student = auth('student')->user();

        return view('student.catalog.books.index', [
            'books' => $this->bookCatalogQuery->paginateFor($student, $request),
        ]);
    }

    public function show(Book $book): View
    {
        $student = auth('student')->user();
        $book->load('product');

        return view('student.catalog.books.show', [
            'book' => $book,
            'inCart' => CartItem::query()
                ->whereHas('cart', fn ($query) => $query->where('student_id', $student->id))
                ->where('product_id', $book->product_id)
                ->exists(),
            'supportedGovernorates' => collect((array) data_get($book->metadata, 'governorates', [])),
        ]);
    }
}
