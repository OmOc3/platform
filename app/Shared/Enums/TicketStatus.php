<?php

namespace App\Shared\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case Assigned = 'assigned';
    case WaitingCustomer = 'waiting_customer';
    case WaitingInternal = 'waiting_internal';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'جديدة',
            self::Assigned => 'مسندة',
            self::WaitingCustomer => 'بانتظار الطالب',
            self::WaitingInternal => 'بانتظار الفريق',
            self::Resolved => 'تم حلها',
            self::Closed => 'مغلقة',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Open => 'warning',
            self::Assigned => 'neutral',
            self::WaitingCustomer => 'success',
            self::WaitingInternal => 'neutral',
            self::Resolved => 'success',
            self::Closed => 'danger',
        };
    }

    public function allowsStudentReply(): bool
    {
        return in_array($this, [
            self::Open,
            self::Assigned,
            self::WaitingCustomer,
            self::WaitingInternal,
        ], true);
    }

    public function allowsAdminReply(): bool
    {
        return $this !== self::Closed;
    }

    /**
     * @return list<string>
     */
    public static function activeWorkloadValues(): array
    {
        return [
            self::Open->value,
            self::Assigned->value,
            self::WaitingCustomer->value,
            self::WaitingInternal->value,
        ];
    }
}
