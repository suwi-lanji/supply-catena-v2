<?php

namespace App\Models;
use Filament\Facades\Filament;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Notifications\Notifiable;
class Team extends Model implements HasAvatar
{

    use HasFactory, Notifiable;
    protected $guarded = [];
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->logo;
    }
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user');
    }
    public function branchUsers() {
        return $this->hasMany(BranchUser::class);
    }
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user');
    }

    public function warehouses(): HasMany {
        return $this->hasMany(Warehouse::class);
    }

    public function admins(): BelongsToMany {
        return $this->belongsToMany(User::class, 'team_admin');
    }
    public function items(): hasMany {
        return $this->hasMany(Item::class);
    }
    public function inventory_adjustment(): hasMany {
        return $this->hasMany(InventoryAdjustment::class);
    }
    public function vendors(): HasMany {
        return $this->hasMany(Vendor::class);
    }
    public function purchaseOrders(): HasMany {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function salesReturns(): HasMany {
        return $this->hasMany(SalesReturns::class);
    }

    public function bills(): HasMany {
        return $this->hasMany(Bill::class);
    }

    public function itemGroups(): HasMany {
        return $this->hasMany(ItemGroup::class);
    }

    public function salesOrders(): HasMany {
        return $this->hasMany(SalesOrder::class);
    }
    public function quotations(): HasMany {
        return $this->hasMany(Quotation::class);
    }
    public function shipments(): hasMany {
        return $this->hasMany(Shipments::class);
    }

    public function packages(): hasMany {
        return $this->hasMany(Packages::class);
    }

    public function invoices(): hasMany {
        return $this->hasMany(Invoices::class);
    }
    public function item_groups(): HasMany {
        return $this->hasMany(ITemGroup::class);
    }

    public function customers(): HasMany {
        return $this->hasMany(Customer::class);
    }
    public function inventoryAdjustments(): HasMany {
        return $this->hasMany(InventoryAdjustment::class);
    }

    public function purchaseReceives(): HasMany {
        return $this->hasMany(PurchaseReceives::class);
    }

    public function paymentsMades(): HasMany {
        return $this->hasMany(PaymentsMade::class);
    }

    public function vendorCredits(): HasMany {
        return $this->hasMany(VendorCredit::class);
    }
    public function creditNotes(): HasMany {
        return $this->hasMany(CreditNotes::class);
    }

    public function paymentsReceiveds(): HasMany {
        return $this->hasMany(PaymentsReceived::class);
    }

    //
    public function sales_persons(): HasMany {
        return $this->hasMany(SalesPerson::class);
    }

    public function sales_accounts(): HasMany {
        return $this->hasMany(SalesAccount::class);
    }

    public function paymentTerms(): HasMany {
        return $this->hasMany(PaymentTerm::class);
    }

    public function manufucturers(): HasMany {
        return $this->hasMany(Manufucturer::class);
    }

    public function brands(): HasMany {
        return $this->hasMany(Brand::class);
    }

    public function delivery_methods(): HasMany {
        return $this->hasMany(DeliveryMethod::class);
    }

    public function salesReceipts(): HasMany {
        return $this->hasMany(SalesReceipt::class);
    }

    public function transferOrders(): HasMany {
        return $this->hasMany(TransferOrder::class);
    }
    public function purchaseOrderShipments(): HasMany {
        return $this->hasMany(PurchaseOrderShipment::class);
    }

    public function itemsSold() {
        return $this->hasMany(ItemsSold::class);
    }

    public function itemsPurchased() {
        return $this->hasMany(ItemsPurchased::class);
    }
}
