<?php

namespace Database\Seeders;

use App\Models\Customers;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\Users;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TicketSampleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Users::query()->firstOrCreate(
            ['email' => 'admin.support@example.com'],
            [
                'name' => 'Support Admin',
                'mobile' => '9000000001',
                'password' => Hash::make('password'),
                'role_type' => 1,
                'status' => 1,
            ]
        );

        $supportAgent = Users::query()->firstOrCreate(
            ['email' => 'agent.support@example.com'],
            [
                'name' => 'Support Agent',
                'mobile' => '9000000002',
                'password' => Hash::make('password'),
                'role_type' => 1,
                'status' => 1,
            ]
        );

        $customerUser = Users::query()->firstOrCreate(
            ['email' => 'customer.ticket@example.com'],
            [
                'name' => 'Ticket Customer',
                'mobile' => '9000000003',
                'password' => Hash::make('password'),
                'role_type' => Users::CUSTOMER_APP_ROLE_TYPE,
                'status' => 1,
                'approval_status' => 'approved',
                'user_type' => 'Retailer',
            ]
        );

        Customers::query()->firstOrCreate(
            ['user_id' => $customerUser->user_id],
            ['customer_address' => 'Sample support ticket address']
        );

        $ticket = Ticket::query()->firstOrCreate(
            ['subject' => 'Unable to complete payment'],
            [
                'user_id' => $customerUser->user_id,
                'type' => 'payment',
                'description' => 'Payment fails after selecting UPI and returns to checkout screen.',
                'status' => Ticket::STATUS_IN_PROGRESS,
                'priority' => Ticket::PRIORITY_HIGH,
                'assigned_to' => $supportAgent->user_id,
            ]
        );

        TicketReply::query()->firstOrCreate(
            [
                'ticket_id' => $ticket->id,
                'user_id' => $customerUser->user_id,
                'message' => 'I am seeing the issue on Android when I try to pay.',
            ],
            [
                'is_admin' => false,
                'is_internal' => false,
            ]
        );

        TicketReply::query()->firstOrCreate(
            [
                'ticket_id' => $ticket->id,
                'user_id' => $admin->user_id,
                'message' => 'We are checking the gateway logs and will update you shortly.',
            ],
            [
                'is_admin' => true,
                'is_internal' => false,
            ]
        );

        TicketReply::query()->firstOrCreate(
            [
                'ticket_id' => $ticket->id,
                'user_id' => $supportAgent->user_id,
                'message' => 'Internal note: reproduce with test UPI flow after cache clear.',
            ],
            [
                'is_admin' => true,
                'is_internal' => true,
            ]
        );
    }
}