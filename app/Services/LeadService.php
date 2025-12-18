<?php
// app/Services/LeadService.php

namespace App\Services;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Collection;

class LeadService
{
    /**
     * Create lead manually
     */
    public function create(User $user, array $data): Lead
    {
        $lead = Lead::create([
            'user_id' => $user->id,
            'subscription_id' => $user->activeSubscription?->id,
            'lead_id' => Lead::generateLeadId(),
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'],
            'alternate_phone' => $data['alternate_phone'] ?? null,
            'source' => $data['source'],
            'campaign_name' => $data['campaign_name'] ?? null,
            'status' => $data['status'] ?? 'new',
            'quality' => $data['quality'] ?? null,
            'notes' => $data['notes'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'lead_created_at' => now(),
        ]);

        // Notify user
        NotificationService::newLeadReceived($user, $lead);

        return $lead;
    }

    /**
     * Create lead from webhook (Meta/Google)
     */
    public function createFromWebhook(User $user, array $data, string $source): Lead
    {
        $lead = Lead::create([
            'user_id' => $user->id,
            'subscription_id' => $user->activeSubscription?->id,
            'lead_id' => $data['leadgen_id'] ?? Lead::generateLeadId(),
            'name' => $data['full_name'] ?? $data['name'] ?? 'Unknown',
            'email' => $data['email'] ?? null,
            'phone' => $data['phone_number'] ?? $data['phone'] ?? null,
            'source' => $source,
            'campaign_name' => $data['campaign_name'] ?? null,
            'ad_name' => $data['ad_name'] ?? null,
            'adset_name' => $data['adset_name'] ?? null,
            'form_name' => $data['form_name'] ?? null,
            'status' => 'new',
            'custom_fields' => $data['custom_fields'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'lead_created_at' => isset($data['created_time']) 
                ? \Carbon\Carbon::parse($data['created_time']) 
                : now(),
        ]);

        // Notify user
        NotificationService::newLeadReceived($user, $lead);

        ActivityLogService::log(
            'lead_received',
            "New lead received from {$source}: {$lead->name}",
            $lead,
            ['source' => $source],
            $user
        );

        return $lead;
    }

    /**
     * Update lead status
     */
    public function updateStatus(Lead $lead, string $status): Lead
    {
        $oldStatus = $lead->status;
        
        $lead->update(['status' => $status]);

        if ($status === 'contacted' && !$lead->contacted_at) {
            $lead->update(['contacted_at' => now()]);
        }

        ActivityLogService::log(
            'lead_status_updated',
            "Lead status changed from {$oldStatus} to {$status}",
            $lead,
            ['old_status' => $oldStatus, 'new_status' => $status]
        );

        return $lead;
    }

    /**
     * Get lead statistics for user
     */
    public function getStats(User $user, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = Lead::where('user_id', $user->id);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $total = $query->count();
        $byStatus = $query->get()->groupBy('status')->map->count();
        $bySource = $query->get()->groupBy('source')->map->count();
        $byQuality = $query->whereNotNull('quality')->get()->groupBy('quality')->map->count();

        return [
            'total' => $total,
            'new' => $byStatus['new'] ?? 0,
            'contacted' => $byStatus['contacted'] ?? 0,
            'qualified' => $byStatus['qualified'] ?? 0,
            'converted' => $byStatus['converted'] ?? 0,
            'lost' => $byStatus['lost'] ?? 0,
            'by_source' => $bySource->toArray(),
            'by_quality' => $byQuality->toArray(),
            'conversion_rate' => $total > 0 
                ? round((($byStatus['converted'] ?? 0) / $total) * 100, 2) 
                : 0,
        ];
    }

    /**
     * Export leads to CSV
     */
    public function exportToCsv(User $user, array $filters = []): string
    {
        $query = Lead::where('user_id', $user->id);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }

        $leads = $query->orderBy('created_at', 'desc')->get();

        $csv = "Name,Email,Phone,Source,Status,Quality,Campaign,Created At\n";

        foreach ($leads as $lead) {
            $csv .= implode(',', [
                '"' . $lead->name . '"',
                '"' . $lead->email . '"',
                '"' . $lead->phone . '"',
                $lead->source,
                $lead->status,
                $lead->quality ?? '',
                '"' . $lead->campaign_name . '"',
                $lead->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return $csv;
    }
}