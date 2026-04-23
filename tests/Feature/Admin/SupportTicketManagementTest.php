<?php

namespace Tests\Feature\Admin;

use App\Modules\Identity\Models\Admin;
use App\Modules\Support\Models\SupportTeam;
use App\Modules\Support\Models\SupportTicket;
use App\Shared\Enums\TicketStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Support\InteractsWithAdminAuth;
use Tests\TestCase;

class SupportTicketManagementTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshDatabase;

    public function test_admin_can_list_assign_update_status_and_reply_to_ticket(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->signInAdmin([
            'tickets.view',
            'tickets.manage',
        ]);

        $ticket = SupportTicket::query()->firstOrFail();
        $team = SupportTeam::query()->firstOrFail();
        $assignee = Admin::factory()->create();

        Permission::findOrCreate('tickets.manage', 'admin');
        $assignee->givePermissionTo(['tickets.manage']);
        $team->admins()->syncWithoutDetaching([$assignee->id]);

        $this->get(route('admin.tickets.index'))
            ->assertOk()
            ->assertSeeText($ticket->subject);

        $this->put(route('admin.tickets.assignment.update', $ticket), [
            'support_team_id' => $team->id,
            'assigned_admin_id' => $assignee->id,
        ])->assertRedirect(route('admin.tickets.show', $ticket));

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'support_team_id' => $team->id,
            'assigned_admin_id' => $assignee->id,
        ]);

        $this->put(route('admin.tickets.status.update', $ticket), [
            'status' => TicketStatus::WaitingInternal->value,
        ])->assertRedirect(route('admin.tickets.show', $ticket));

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::WaitingInternal->value,
        ]);

        $admin = auth('admin')->user();

        $this->post(route('admin.tickets.reply', $ticket), [
            'body' => 'تمت مراجعة التذكرة وتحويلها للفريق المختص، وسنوافيك بالتحديث التالي قريبًا.',
        ])->assertRedirect(route('admin.tickets.show', $ticket));

        $this->assertDatabaseHas('support_ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'author_id' => $admin->id,
            'author_type' => $admin->getMorphClass(),
            'is_staff_reply' => true,
        ]);
        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::WaitingCustomer->value,
        ]);
    }

    public function test_ticket_routes_are_permission_protected(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->signInAdmin(['dashboard.view']);

        $ticket = SupportTicket::query()->firstOrFail();

        $this->get(route('admin.tickets.index'))->assertForbidden();
        $this->get(route('admin.tickets.show', $ticket))->assertForbidden();
        $this->put(route('admin.tickets.status.update', $ticket), [
            'status' => TicketStatus::Resolved->value,
        ])->assertForbidden();
        $this->post(route('admin.tickets.reply', $ticket), [
            'body' => 'محاولة رد بدون صلاحية.',
        ])->assertForbidden();
    }
}
