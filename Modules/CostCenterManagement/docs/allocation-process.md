# Allocation Process Controller

## Overview
Controller untuk mengelola proses alokasi biaya antar cost center. Mendukung execution, monitoring, review, posting, dan rollback.

## Features

### 1. List Allocation Batches
- **Route**: `GET /m/cost-center-management/allocation-process`
- **Method**: `index()`
- Menampilkan daftar semua batch alokasi dengan summary
- Pagination 20 items per page
- Menampilkan: batch_id, periode, status, jumlah journal, total alokasi

### 2. Create New Allocation
- **Route**: `GET /m/cost-center-management/allocation-process/create`
- **Method**: `create()`
- Form untuk setup periode alokasi baru
- Default: periode bulan berjalan

### 3. Execute Allocation
- **Route**: `POST /m/cost-center-management/allocation-process/execute`
- **Method**: `execute(Request $request)`
- **Parameters**:
  - `period_start` (required, date)
  - `period_end` (required, date, after_or_equal:period_start)
- Menjalankan proses alokasi untuk periode yang dipilih
- Menggunakan `CostAllocationService::executeAllocation()`
- Generate batch_id otomatis
- Redirect ke review page setelah selesai

### 4. Real-time Status Monitoring
- **Route**: `GET /m/cost-center-management/allocation-process/status`
- **Method**: `status(Request $request)`
- **Parameters**:
  - `cache_key` (required)
- Return JSON dengan status proses
- Menggunakan Laravel Cache untuk tracking progress
- Response format:
```json
{
  "success": true,
  "data": {
    "status": "processing|completed|failed",
    "progress": 0-100,
    "message": "Status message",
    "batch_id": "ALLOC-20260215..."
  }
}
```

### 5. Review Allocation Results
- **Route**: `GET /m/cost-center-management/allocation-process/{batchId}/review`
- **Method**: `review(string $batchId)`
- Menampilkan detail hasil alokasi sebelum posting
- Summary: total source, total allocated, difference (zero-sum check)
- Grouped by source cost center
- Detail per journal entry
- Actions: Post ke GL atau Rollback

### 6. Post to GL
- **Route**: `POST /m/cost-center-management/allocation-process/{batchId}/post`
- **Method**: `post(string $batchId)`
- Posting allocation journals ke General Ledger
- Validasi zero-sum sebelum posting
- Update status dari 'draft' ke 'posted'
- Record posted_at dan posted_by
- **TODO**: Integration dengan GL module

### 7. Rollback Allocation
- **Route**: `POST /m/cost-center-management/allocation-process/{batchId}/rollback`
- **Method**: `rollback(string $batchId)`
- Rollback allocation batch
- Jika status 'draft': delete journals
- Jika status 'posted': create reversal entries (update status ke 'reversed')
- **TODO**: Create reversal GL entries

## Workflow

```
1. User creates new allocation (create)
   ↓
2. System executes allocation (execute)
   ↓
3. System generates allocation journals (draft status)
   ↓
4. User reviews results (review)
   ↓
5a. User posts to GL (post) → status: posted
   OR
5b. User rollbacks (rollback) → journals deleted/reversed
```

## Validation Rules

### Execute Allocation
- period_start: required, date
- period_end: required, date, after_or_equal:period_start

### Post to GL
- Batch must exist
- Status must be 'draft'
- Zero-sum validation must pass (difference <= 0.01)

### Rollback
- Batch must exist
- Cannot rollback already reversed batch

## Error Handling

### Execute Errors
- Allocation rule validation failed
- Source cost center inactive
- Target cost center inactive
- Zero-sum validation failed
- Database transaction errors

### Post Errors
- No draft journals found
- Zero-sum validation failed
- Database transaction errors

### Rollback Errors
- Batch not found
- Already reversed
- Database transaction errors

## Dependencies

- `CostAllocationService`: Core allocation logic
- `AllocationJournal` model: Journal entries
- Laravel Cache: Progress tracking
- Laravel DB: Transaction management

## Future Enhancements

1. Queue job untuk long-running allocations
2. Real-time progress updates via WebSocket/Pusher
3. Email notification setelah allocation selesai
4. Export allocation results ke Excel/PDF
5. Approval workflow sebelum posting
6. Integration dengan GL module
7. Audit trail untuk semua actions
8. Batch scheduling (cron job)

## Testing

Lihat test files:
- Unit tests: `tests/Unit/AllocationCalculationTest.php`
- Property tests: `tests/Unit/AllocationZeroSumPropertyTest.php`

## Requirements Validation

Task ini memenuhi requirements:
- **6.1**: Execute allocation process otomatis
- **6.2**: Allocate dari Administrative ke Medical/Non-Medical
- **6.3**: Step-down allocation method support
- **6.6**: Rollback functionality
