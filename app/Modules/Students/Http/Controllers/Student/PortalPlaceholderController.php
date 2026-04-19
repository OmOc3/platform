<?php

namespace App\Modules\Students\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class PortalPlaceholderController extends Controller
{
    public function show(string $section): View
    {
        $map = [
            'lectures' => ['title' => 'المحاضرات', 'description' => 'سيتم هنا عرض المحاضرات والمراجعات والامتحانات بعد تفعيل المرحلة الأكاديمية التالية.'],
            'packages' => ['title' => 'الباقات', 'description' => 'هذه الصفحة ستكون واجهة شراء واستعراض الباقات الرقمية في المرحلة القادمة.'],
            'books' => ['title' => 'الكتب', 'description' => 'هذه الصفحة ستكون واجهة كتالوج الكتب وطلبات الشحن في المرحلة القادمة.'],
            'forum' => ['title' => 'ملتقى الأسئلة', 'description' => 'هذه الصفحة ستكون ملتقى الأسئلة والردود والملفات المرفقة.'],
            'mistakes' => ['title' => 'أخطائي', 'description' => 'هذه الصفحة ستكون مركز تتبع الأخطاء وربطها بالمحتوى والاختبارات.'],
            'cart' => ['title' => 'السلة', 'description' => 'هذه الصفحة ستكون نقطة تجميع مشتريات الطالب قبل الدفع.'],
        ];

        abort_unless(isset($map[$section]), 404);

        return view('student.portal.placeholder', [
            'section' => $section,
            'title' => $map[$section]['title'],
            'description' => $map[$section]['description'],
        ]);
    }
}
