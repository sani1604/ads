<?php
// app/Services/CreativeService.php

namespace App\Services;

use App\Models\Creative;
use App\Models\CreativeFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreativeService
{
    /**
     * Create a new creative with files
     */
    public function create(User $user, array $data, array $files): Creative
    {
        $creative = Creative::create([
            'user_id' => $user->id,
            'subscription_id' => $user->activeSubscription?->id,
            'service_category_id' => $data['service_category_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'platform' => $data['platform'],
            'status' => 'draft',
            'version' => 1,
            'ad_copy' => $data['ad_copy'] ?? null,
            'cta_text' => $data['cta_text'] ?? null,
            'landing_url' => $data['landing_url'] ?? null,
            'scheduled_date' => $data['scheduled_date'] ?? null,
        ]);

        // Upload files
        foreach ($files as $index => $file) {
            $this->uploadFile($creative, $file, $index);
        }

        return $creative->fresh(['files']);
    }

    /**
     * Update creative
     */
    public function update(Creative $creative, array $data, ?array $files = null): Creative
    {
        $creative->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'platform' => $data['platform'],
            'ad_copy' => $data['ad_copy'] ?? null,
            'cta_text' => $data['cta_text'] ?? null,
            'landing_url' => $data['landing_url'] ?? null,
            'scheduled_date' => $data['scheduled_date'] ?? null,
        ]);

        // Upload new files if provided
        if ($files) {
            $startOrder = $creative->files()->max('sort_order') + 1;
            foreach ($files as $index => $file) {
                $this->uploadFile($creative, $file, $startOrder + $index);
            }
        }

        return $creative->fresh(['files']);
    }

    /**
     * Upload file for creative
     */
    protected function uploadFile(Creative $creative, UploadedFile $file, int $sortOrder): CreativeFile
    {
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "creatives/{$creative->user_id}/{$creative->id}";
        
        $file->storeAs($path, $fileName, 'public');

        return CreativeFile::create([
            'creative_id' => $creative->id,
            'file_name' => $fileName,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => "{$path}/{$fileName}",
            'file_type' => $file->getClientMimeType(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Delete file from creative
     */
    public function deleteFile(CreativeFile $file): bool
    {
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        return $file->delete();
    }

    /**
     * Submit for approval
     */
    public function submitForApproval(Creative $creative): Creative
    {
        $creative->submitForApproval();

        // Notify admins
        $admins = User::admins()->get();
        foreach ($admins as $admin) {
            NotificationService::newCreativeForApproval($admin, $creative);
        }

        ActivityLogService::log(
            'creative_submitted',
            "Creative '{$creative->title}' submitted for approval",
            $creative,
            [],
            $creative->user
        );

        return $creative;
    }

    /**
     * Approve creative
     */
    public function approve(Creative $creative, User $approver): Creative
    {
        $creative->approve($approver);

        // Notify client
        NotificationService::creativeApproved($creative->user, $creative);

        ActivityLogService::log(
            'creative_approved',
            "Creative '{$creative->title}' approved",
            $creative,
            ['approved_by' => $approver->name],
            $approver
        );

        return $creative;
    }

    /**
     * Request changes
     */
    public function requestChanges(Creative $creative, User $reviewer): Creative
    {
        $creative->requestChanges();

        // Notify client
        NotificationService::creativeNeedsChanges($creative->user, $creative);

        ActivityLogService::log(
            'creative_changes_requested',
            "Changes requested for creative '{$creative->title}'",
            $creative,
            [],
            $reviewer
        );

        return $creative;
    }

    /**
     * Create new version of creative
     */
    public function createNewVersion(Creative $creative): Creative
    {
        $newVersion = $creative->createNewVersion();

        // Copy files to new version
        foreach ($creative->files as $file) {
            $newPath = "creatives/{$creative->user_id}/{$newVersion->id}";
            $newFileName = Str::uuid() . '.' . pathinfo($file->original_name, PATHINFO_EXTENSION);

            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->copy($file->file_path, "{$newPath}/{$newFileName}");

                CreativeFile::create([
                    'creative_id' => $newVersion->id,
                    'file_name' => $newFileName,
                    'original_name' => $file->original_name,
                    'file_path' => "{$newPath}/{$newFileName}",
                    'file_type' => $file->file_type,
                    'mime_type' => $file->mime_type,
                    'file_size' => $file->file_size,
                    'sort_order' => $file->sort_order,
                ]);
            }
        }

        return $newVersion->fresh(['files']);
    }
}