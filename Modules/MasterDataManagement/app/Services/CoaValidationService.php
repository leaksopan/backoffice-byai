<?php

namespace Modules\MasterDataManagement\Services;

use Modules\MasterDataManagement\Models\MdmChartOfAccount;

class CoaValidationService
{
    /**
     * Validate COA code format: X-XX-XX-XX-XXX
     * 
     * @param string $code
     * @return bool
     */
    public function validateCoaFormat(string $code): bool
    {
        // Format: X-XX-XX-XX-XXX (digit-digit-digit-digit-digit dengan separator -)
        $pattern = '/^\d{1}-\d{2}-\d{2}-\d{2}-\d{3}$/';
        return preg_match($pattern, $code) === 1;
    }

    /**
     * Check if account can be used for transaction posting
     * Header accounts (with children) cannot be posted to
     * 
     * @param MdmChartOfAccount $account
     * @return bool
     */
    public function canPostTransaction(MdmChartOfAccount $account): bool
    {
        return !$account->is_header;
    }

    /**
     * Check if account can be deleted
     * Cannot delete if:
     * - Has children accounts
     * - Used in transactions (would need to check transaction tables)
     * 
     * @param MdmChartOfAccount $account
     * @return bool
     */
    public function canDelete(MdmChartOfAccount $account): bool
    {
        // Check if has children
        if ($account->hasChildren()) {
            return false;
        }

        // TODO: Check if used in transactions when transaction tables exist
        // For now, just check children
        return true;
    }

    /**
     * Parse COA structure from code
     * Returns array with: kelompok, jenis, objek, rincian_objek, sub_rincian_objek
     * 
     * @param string $code
     * @return array
     */
    public function parseCoaStructure(string $code): array
    {
        if (!$this->validateCoaFormat($code)) {
            return [];
        }

        $parts = explode('-', $code);

        return [
            'kelompok' => $parts[0],
            'jenis' => $parts[1],
            'objek' => $parts[2],
            'rincian_objek' => $parts[3],
            'sub_rincian_objek' => $parts[4],
        ];
    }
}
