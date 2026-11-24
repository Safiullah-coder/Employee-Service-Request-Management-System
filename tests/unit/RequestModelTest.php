<?php

use App\Models\RequestModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class RequestModelTest extends CIUnitTestCase
{
    public function testHighPriorityUsesHalfTheCategorySla(): void
    {
        $startedAt = time();
        $dueAt = strtotime(RequestModel::calcDueAt(24, 'high'));

        $this->assertGreaterThanOrEqual($startedAt + 43199, $dueAt);
        $this->assertLessThanOrEqual($startedAt + 43201, $dueAt);
    }

    public function testHighPriorityPreservesHalfHoursForOddSlaValues(): void
    {
        $startedAt = time();
        $dueAt = strtotime(RequestModel::calcDueAt(5, 'high'));

        $this->assertGreaterThanOrEqual($startedAt + 8999, $dueAt);
        $this->assertLessThanOrEqual($startedAt + 9001, $dueAt);
    }

    public function testStatusTransitionsMustFollowTheDefinedOrder(): void
    {
        $this->assertTrue(RequestModel::isValidTransition('open', 'in_progress'));
        $this->assertTrue(RequestModel::isValidTransition('in_progress', 'resolved'));
        $this->assertTrue(RequestModel::isValidTransition('resolved', 'closed'));
        $this->assertFalse(RequestModel::isValidTransition('open', 'closed'));
        $this->assertFalse(RequestModel::isValidTransition('closed', 'open'));
    }

    public function testOverdueRequestsExcludeCompletedStatuses(): void
    {
        $pastDue = date('Y-m-d H:i:s', time() - 3600);

        $this->assertTrue(RequestModel::isOverdue(['status' => 'open', 'due_at' => $pastDue]));
        $this->assertFalse(RequestModel::isOverdue(['status' => 'resolved', 'due_at' => $pastDue]));
        $this->assertFalse(RequestModel::isOverdue(['status' => 'closed', 'due_at' => $pastDue]));
        $this->assertFalse(RequestModel::isOverdue(['status' => 'open', 'due_at' => null]));
        $this->assertFalse(RequestModel::isOverdue(['status' => 'open', 'due_at' => date('Y-m-d H:i:s', time() + 3600)]));
    }
}
