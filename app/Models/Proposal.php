<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'client_id',
        'template_id',
        'proposal_number',
        'proposal_date',
        'valid_until',
        'status',
        'tags',
        'notes',
        'currency_id',
        'subtotal',
        'tax_total',
        'total',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'proposal_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Get the client for this proposal.
     */
    public function client()
    {
        return $this->belongsTo(\App\Models\Admin\Client::class);
    }

    /**
     * Get the template for this proposal.
     */
    public function template()
    {
        return $this->belongsTo(ProposalTemplate::class, 'template_id');
    }

    /**
     * Get the currency for this proposal.
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the items for this proposal.
     */
    public function items()
    {
        return $this->hasMany(ProposalItem::class)->orderBy('position');
    }

    /**
     * Get the history entries for this proposal.
     */
    public function history()
    {
        return $this->hasMany(ProposalHistory::class)->orderBy('event_date', 'desc');
    }

    /**
     * Get the user who created the proposal.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the proposal.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Generate a unique proposal number.
     */
    public static function generateProposalNumber(): string
    {
        $year = date('Y');
        $lastProposal = self::where('proposal_number', 'like', "PROP-{$year}-%")
            ->orderBy('proposal_number', 'desc')
            ->first();

        if ($lastProposal) {
            $lastNumber = intval(substr($lastProposal->proposal_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('PROP-%s-%04d', $year, $newNumber);
    }

    /**
     * Calculate totals for the proposal.
     */
    public function calculateTotals(): void
    {
        $subtotal = 0;
        $taxTotal = 0;

        foreach ($this->items as $item) {
            $itemTotal = $item->quantity * $item->unit_price;
            $itemTax = $itemTotal * ($item->tax_rate / 100);
            
            $item->tax_amount = $itemTax;
            $item->total = $itemTotal + $itemTax;
            $item->save();

            $subtotal += $itemTotal;
            $taxTotal += $itemTax;
        }

        $this->subtotal = $subtotal;
        $this->tax_total = $taxTotal;
        $this->total = $subtotal + $taxTotal;
        $this->save();
    }

    /**
     * Check if proposal is expired.
     */
    public function isExpired(): bool
    {
        return $this->valid_until < Carbon::today() && $this->status !== 'expired';
    }

    /**
     * Check if proposal can be sent.
     */
    public function canBeSent(): bool
    {
        return $this->status === 'draft' && $this->items()->count() > 0;
    }

    /**
     * Check if proposal can be accepted.
     */
    public function canBeAccepted(): bool
    {
        return $this->status === 'sent' && !$this->isExpired();
    }

    /**
     * Check if proposal can be rejected.
     */
    public function canBeRejected(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * Get the formatted proposal number for display.
     */
    public function getFormattedNumberAttribute(): string
    {
        return $this->proposal_number;
    }

    /**
     * Get client full name.
     */
    public function getClientFullNameAttribute(): string
    {
        if (!$this->client) {
            return '-';
        }
        return $this->client->first_name . ' ' . $this->client->last_name;
    }

    /**
     * Scope a query to only include proposals by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include expired proposals.
     */
    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<', now())
                    ->where('status', '!=', 'expired');
    }
}

