<?php

namespace App\Modules\Commerce\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Commerce\Models\Book;
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
        return view('student.catalog.books.index', [
            'books' => $this->bookCatalogQuery->paginate($request),
        ]);
    }

    public function show(Book $book): View
    {
        return view('student.catalog.books.show', [
            'book' => $book->load('product'),
        ]);
    }
}
