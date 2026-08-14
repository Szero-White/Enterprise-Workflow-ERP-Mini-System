<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'role_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function workflowRequests()
    {
        return $this->hasMany(WorkflowRequest::class, 'created_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }

    public function systemNotifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'created_by');
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class, 'received_by');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'created_by');
    }

    public function assignedAssets()
    {
        return $this->hasMany(AssetAssignment::class, 'assigned_to');
    }

    public function assetAssignmentsCreated()
    {
        return $this->hasMany(AssetAssignment::class, 'assigned_by');
    }

    public function assetReturnsReceived()
    {
        return $this->hasMany(AssetReturn::class, 'received_by');
    }

    public function uploadedAttachments()
    {
        return $this->hasMany(Attachment::class, 'uploaded_by');
    }

    public function workflowStepsAsApprover()
    {
        return $this->hasMany(WorkflowStep::class, 'approver_user_id');
    }

    public function hasOperationalHistory(): bool
    {
        return $this->workflowRequests()->exists()
            || $this->auditLogs()->exists()
            || $this->uploadedAttachments()->exists()
            || $this->workflowStepsAsApprover()->exists()
            || $this->purchaseOrders()->exists()
            || $this->goodsReceipts()->exists()
            || $this->inventoryMovements()->exists()
            || $this->assignedAssets()->exists()
            || $this->assetAssignmentsCreated()->exists()
            || $this->assetReturnsReceived()->exists();
    }

    public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return $this->role && in_array($this->role->key, $roles, true);
    }
}
