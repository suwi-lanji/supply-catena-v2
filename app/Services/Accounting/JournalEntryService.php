<?php

namespace App\Services\Accounting;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\LedgerAccount;
use App\Models\AccountTransaction;
use App\Models\Team;
use App\Services\BaseService;
use App\DTOs\JournalEntryDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Exception;

class JournalEntryService extends BaseService
{
    /**
     * Create a new journal entry.
     *
     * @param Team $team
     * @param array $data
     * @return JournalEntry
     * @throws Exception
     */
    public function create(Team $team, array $data): JournalEntry
    {
        return $this->transaction(function () use ($team, $data) {
            $entry = JournalEntry::create([
                'team_id' => $team->id,
                'entry_date' => $data['entry_date'] ?? now(),
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => JournalEntry::STATUS_DRAFT,
                'created_by' => $data['user_id'] ?? null,
            ]);

            // Create lines
            foreach ($data['lines'] as $lineData) {
                $this->addLine($entry, $lineData);
            }

            $this->logAction('journal_entry_created', [
                'entry_id' => $entry->id,
                'entry_number' => $entry->entry_number,
            ]);

            return $entry;
        });
    }

    /**
     * Add a line to a journal entry.
     *
     * @param JournalEntry $entry
     * @param array $lineData
     * @return JournalEntryLine
     */
    public function addLine(JournalEntry $entry, array $lineData): JournalEntryLine
    {
        return $entry->lines()->create([
            'ledger_account_id' => $lineData['ledger_account_id'],
            'type' => $lineData['type'],
            'amount' => $lineData['amount'],
            'description' => $lineData['description'] ?? null,
        ]);
    }

    /**
     * Post a journal entry.
     *
     * @param JournalEntry $entry
     * @param int $userId
     * @return bool
     * @throws Exception
     */
    public function post(JournalEntry $entry, int $userId): bool
    {
        if (!$entry->canBePosted()) {
            throw new Exception('Journal entry cannot be posted. It may be unbalanced or already posted.');
        }

        return $this->transaction(function () use ($entry, $userId) {
            $entry->post($userId);

            $this->logAction('journal_entry_posted', [
                'entry_id' => $entry->id,
                'entry_number' => $entry->entry_number,
                'posted_by' => $userId,
            ]);

            return true;
        });
    }

    /**
     * Void a journal entry.
     *
     * @param JournalEntry $entry
     * @param int $userId
     * @param string $reason
     * @return bool
     * @throws Exception
     */
    public function void(JournalEntry $entry, int $userId, string $reason): bool
    {
        if (!$entry->canBeVoided()) {
            throw new Exception('Journal entry cannot be voided.');
        }

        return $this->transaction(function () use ($entry, $userId, $reason) {
            $entry->void($userId, $reason);

            $this->logAction('journal_entry_voided', [
                'entry_id' => $entry->id,
                'entry_number' => $entry->entry_number,
                'voided_by' => $userId,
                'reason' => $reason,
            ]);

            return true;
        });
    }

    /**
     * Create and post a journal entry in one operation.
     *
     * @param Team $team
     * @param array $data
     * @param int $userId
     * @return JournalEntry
     * @throws Exception
     */
    public function createAndPost(Team $team, array $data, int $userId): JournalEntry
    {
        $entry = $this->create($team, $data);
        $this->post($entry, $userId);

        return $entry;
    }

    /**
     * Create a simple journal entry with debit and credit accounts.
     *
     * @param Team $team
     * @param int $debitAccountId
     * @param int $creditAccountId
     * @param float $amount
     * @param array $options
     * @return JournalEntry
     * @throws Exception
     */
    public function createSimple(
        Team $team,
        int $debitAccountId,
        int $creditAccountId,
        float $amount,
        array $options = []
    ): JournalEntry {
        $data = [
            'entry_date' => $options['entry_date'] ?? now(),
            'description' => $options['description'] ?? null,
            'reference_type' => $options['reference_type'] ?? null,
            'reference_id' => $options['reference_id'] ?? null,
            'user_id' => $options['user_id'] ?? null,
            'lines' => [
                [
                    'ledger_account_id' => $debitAccountId,
                    'type' => JournalEntryLine::TYPE_DEBIT,
                    'amount' => $amount,
                    'description' => $options['debit_description'] ?? null,
                ],
                [
                    'ledger_account_id' => $creditAccountId,
                    'type' => JournalEntryLine::TYPE_CREDIT,
                    'amount' => $amount,
                    'description' => $options['credit_description'] ?? null,
                ],
            ],
        ];

        return $this->create($team, $data);
    }

    /**
     * Create a compound journal entry with multiple lines.
     *
     * @param Team $team
     * @param array $debitLines [[account_id, amount, description?], ...]
     * @param array $creditLines [[account_id, amount, description?], ...]
     * @param array $options
     * @return JournalEntry
     * @throws Exception
     */
    public function createCompound(
        Team $team,
        array $debitLines,
        array $creditLines,
        array $options = []
    ): JournalEntry {
        $lines = [];

        foreach ($debitLines as $line) {
            $lines[] = [
                'ledger_account_id' => $line[0],
                'type' => JournalEntryLine::TYPE_DEBIT,
                'amount' => $line[1],
                'description' => $line[2] ?? null,
            ];
        }

        foreach ($creditLines as $line) {
            $lines[] = [
                'ledger_account_id' => $line[0],
                'type' => JournalEntryLine::TYPE_CREDIT,
                'amount' => $line[1],
                'description' => $line[2] ?? null,
            ];
        }

        $data = [
            'entry_date' => $options['entry_date'] ?? now(),
            'description' => $options['description'] ?? null,
            'reference_type' => $options['reference_type'] ?? null,
            'reference_id' => $options['reference_id'] ?? null,
            'user_id' => $options['user_id'] ?? null,
            'lines' => $lines,
        ];

        return $this->create($team, $data);
    }

    /**
     * Validate that debits equal credits.
     *
     * @param array $lines
     * @return bool
     */
    public function validateBalance(array $lines): bool
    {
        $debits = 0;
        $credits = 0;

        foreach ($lines as $line) {
            if ($line['type'] === JournalEntryLine::TYPE_DEBIT) {
                $debits += $line['amount'];
            } else {
                $credits += $line['amount'];
            }
        }

        return bccomp((string) $debits, (string) $credits, 2) === 0;
    }

    /**
     * Get journal entries for a reference model.
     *
     * @param mixed $model
     * @return Collection
     */
    public function getEntriesForReference(mixed $model): Collection
    {
        return JournalEntry::where('reference_type', get_class($model))
            ->where('reference_id', $model->id)
            ->with('lines.ledgerAccount')
            ->get();
    }

    /**
     * Get the trial balance for a team.
     *
     * @param Team $team
     * @param Carbon|null $asOf
     * @return array
     */
    public function getTrialBalance(Team $team, ?Carbon $asOf = null): array
    {
        $asOf = $asOf ?? now();

        $accounts = LedgerAccount::where('team_id', $team->id)
            ->active()
            ->with(['transactions' => function ($query) use ($asOf) {
                $query->where('transaction_date', '<=', $asOf);
            }])
            ->get();

        $trialBalance = [
            'debit_total' => 0,
            'credit_total' => 0,
            'accounts' => [],
        ];

        foreach ($accounts as $account) {
            $debits = $account->transactions->where('type', 'debit')->sum('amount');
            $credits = $account->transactions->where('type', 'credit')->sum('amount');

            if ($account->isDebitAccount()) {
                $balance = $account->opening_balance + $debits - $credits;
                if ($balance != 0) {
                    $trialBalance['accounts'][] = [
                        'account' => $account,
                        'debit' => $balance > 0 ? abs($balance) : 0,
                        'credit' => $balance < 0 ? abs($balance) : 0,
                    ];
                    if ($balance > 0) {
                        $trialBalance['debit_total'] += abs($balance);
                    } else {
                        $trialBalance['credit_total'] += abs($balance);
                    }
                }
            } else {
                $balance = $account->opening_balance + $credits - $debits;
                if ($balance != 0) {
                    $trialBalance['accounts'][] = [
                        'account' => $account,
                        'debit' => $balance < 0 ? abs($balance) : 0,
                        'credit' => $balance > 0 ? abs($balance) : 0,
                    ];
                    if ($balance < 0) {
                        $trialBalance['debit_total'] += abs($balance);
                    } else {
                        $trialBalance['credit_total'] += abs($balance);
                    }
                }
            }
        }

        return $trialBalance;
    }
}
