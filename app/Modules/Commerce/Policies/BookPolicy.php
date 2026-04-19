<?php

namespace App\Modules\Commerce\Policies;

use App\Modules\Commerce\Models\Book;
use App\Modules\Identity\Models\Admin;

class BookPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('books.view');
    }

    public function view(Admin $admin, Book $book): bool
    {
        return $admin->can('books.view');
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('books.manage');
    }

    public function update(Admin $admin, Book $book): bool
    {
        return $admin->can('books.manage');
    }

    public function delete(Admin $admin, Book $book): bool
    {
        return $admin->can('books.manage');
    }
}
