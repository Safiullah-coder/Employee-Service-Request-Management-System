<?php

namespace App\Controllers;

use App\Models\RequestModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $role = session()->get('role');
        if ($role === 'admin') {
            return redirect()->to(site_url('admin/dashboard'));
        } elseif ($role === 'agent') {
            return redirect()->to(site_url('agent/requests'));
        }

        $requestModel = new RequestModel();
        $requests = $requestModel->getWithDetails(['r.user_id' => session()->get('user_id')]);
        $counts = array_fill_keys(RequestModel::STATUS_FLOW, 0);
        $overdueRequests = [];

        foreach ($requests as $request) {
            $counts[$request['status']]++;
            if (RequestModel::isOverdue($request)) {
                $overdueRequests[] = $request;
            }
        }

        $resolvedLogs = db_connect()->table('activity_logs al')
            ->select('al.created_at as resolved_at, r.created_at as request_created_at')
            ->join('requests r', 'r.id = al.request_id')
            ->where('r.user_id', session()->get('user_id'))
            ->where('al.field', 'status')
            ->where('al.new_value', 'resolved')
            ->get()->getResultArray();

        $monthStart = strtotime(date('Y-m-01 00:00:00'));
        $resolvedThisMonth = 0;
        $totalResolutionHours = 0;
        foreach ($resolvedLogs as $log) {
            $resolvedAt = strtotime($log['resolved_at']);
            $createdAt = strtotime($log['request_created_at']);
            if ($resolvedAt >= $monthStart) {
                $resolvedThisMonth++;
            }
            $totalResolutionHours += max(0, $resolvedAt - $createdAt) / 3600;
        }

        $stats = [
            'open' => $counts['open'] + $counts['in_progress'],
            'resolved_this_month' => $resolvedThisMonth,
            'overdue' => count($overdueRequests),
            'average_resolution_hours' => $resolvedLogs
                ? (int) round($totalResolutionHours / count($resolvedLogs))
                : null,
        ];
        $statusChart = array_map(
            static fn (string $status): array => [
                'label' => ucfirst(str_replace('_', ' ', $status)),
                'total' => $counts[$status],
            ],
            RequestModel::STATUS_FLOW
        );

        return view('dashboard/employee', [
            'requests' => $requests,
            'recentRequests' => array_slice($requests, 0, 5),
            'overdueRequest' => $overdueRequests[0] ?? null,
            'stats' => $stats,
            'statusChart' => $statusChart,
            'counts' => $counts,
        ]);
    }
}
