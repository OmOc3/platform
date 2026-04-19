<?php

namespace App\Shared\Enums;

enum ProductKind: string
{
    case Lecture = 'lecture';
    case Package = 'package';
    case Book = 'book';
    case Camp = 'camp';
}
