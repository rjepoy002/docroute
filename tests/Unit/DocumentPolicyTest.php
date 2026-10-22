<?php

namespace Tests\Unit;

use App\Models\Document;
use App\Models\DocumentRoute;
use App\Models\User;
use App\Policies\DocumentPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DocumentPolicyTest extends TestCase
{
    public static function assignmentStates(): array
    {
        return [
            'pending owner' => ['pending', 7, true, true, false],
            'received owner' => ['received', 7, true, false, true],
            'closed owner' => ['closed', 7, true, false, false],
            'declined former owner' => ['declined', 7, true, false, false],
            'forwarded former owner' => ['forwarded', 7, true, false, false],
            'other recipient pending' => ['pending', 8, true, false, false],
            'other recipient received' => ['received', 8, true, false, false],
            'inactive owner' => ['pending', 7, false, false, false],
        ];
    }

    #[DataProvider('assignmentStates')]
    public function test_routing_permissions_have_no_administrator_bypass(string $status, int $recipient, bool $active, bool $pendingActions, bool $receivedActions): void
    {
        $policy = new DocumentPolicy;
        $document = new Document;
        $document->setRelation('currentRoute', new DocumentRoute(['to_user_id' => $recipient, 'status' => $status]));
        foreach (['employee', 'admin'] as $role) {
            $user = (new User)->forceFill(['id' => 7, 'role' => $role, 'is_active' => $active]);
            $this->assertSame($pendingActions, $policy->receive($user, $document));
            $this->assertSame($pendingActions, $policy->decline($user, $document));
            $this->assertSame($receivedActions, $policy->forward($user, $document));
            $this->assertSame($receivedActions, $policy->close($user, $document));
        }
    }
}
