# Manual Testing Checklist - Master Data Management

## Setup
- [ ] Database migrated successfully
- [ ] Permissions seeded (access master-data-management, master-data-management.view, create, edit, delete)
- [ ] Module registered in modules table
- [ ] Menu entries created
- [ ] Admin user has all permissions

## 1. Organization Unit Management

### CRUD Operations
- [ ] Can access organization unit list page
- [ ] Can create new organization unit with all required fields
- [ ] Can edit existing organization unit
- [ ] Can view organization unit details
- [ ] Can delete organization unit (only if no children)
- [ ] Cannot delete organization unit with children

### Validation Rules
- [ ] Code must be unique
- [ ] Name is required
- [ ] Type is required (installation/department/unit/section)
- [ ] Cannot set self as parent
- [ ] Cannot create circular reference in hierarchy
- [ ] Hierarchy path updates automatically when parent changes

### Permission Checks
- [ ] User without 'access master-data-management' cannot access module
- [ ] User without 'master-data-management.create' cannot create
- [ ] User without 'master-data-management.edit' cannot edit
- [ ] User without 'master-data-management.delete' cannot delete

## 2. Chart of Accounts Management

### CRUD Operations
- [ ] Can access COA list page
- [ ] Can create new COA with valid format
- [ ] Can edit existing COA
- [ ] Can view COA details
- [ ] Can delete COA (only if not used)
- [ ] Cannot delete COA that is used in transactions

### Validation Rules
- [ ] Code must follow format X-XX-XX-XX-XXX
- [ ] Code must be unique
- [ ] Name is required
- [ ] Category is required (asset/liability/equity/revenue/expense)
- [ ] Normal balance is required (debit/credit)
- [ ] Header accounts cannot be used for posting

### Export/Import
- [ ] Can export COA to Excel
- [ ] Can import COA from Excel
- [ ] Import validates format before saving
- [ ] Import shows error for invalid data

## 3. Funding Source Management

### CRUD Operations
- [ ] Can access funding source list page
- [ ] Can create new funding source
- [ ] Can edit existing funding source
- [ ] Can view funding source details
- [ ] Can delete funding source (only if not used)

### Validation Rules
- [ ] Code must be unique
- [ ] Name is required
- [ ] Type is required
- [ ] Start date is required
- [ ] End date must be after start date (if provided)
- [ ] Inactive funding sources cannot be used in transactions
- [ ] Funding sources outside validity period cannot be used

### Filtering
- [ ] Can filter by type
- [ ] Can filter by active status
- [ ] Can filter by date range

## 4. Service Catalog Management

### CRUD Operations
- [ ] Can access service catalog list page
- [ ] Can create new service
- [ ] Can edit existing service
- [ ] Can view service details
- [ ] Can delete service (only if no tariffs)

### Validation Rules
- [ ] Code must be unique
- [ ] Name is required
- [ ] Category is required
- [ ] Unit (organization unit) is required
- [ ] INA-CBG code is optional

### Filtering
- [ ] Can filter by category
- [ ] Can filter by unit
- [ ] Can search by code or name

## 5. Tariff Management

### CRUD Operations
- [ ] Can access tariff list page
- [ ] Can create new tariff with breakdown
- [ ] Can edit existing tariff
- [ ] Can view tariff details with breakdown
- [ ] Can delete tariff
- [ ] Can view tariff history for a service

### Validation Rules
- [ ] Service is required
- [ ] Service class is required
- [ ] Tariff amount is required and must be positive
- [ ] Start date is required
- [ ] Cannot have overlapping periods for same service/class/payer combination
- [ ] Breakdown components sum should match tariff amount (warning if not)

### Tariff Breakdown
- [ ] Can add multiple breakdown components
- [ ] Can specify component type (jasa_medis, jasa_sarana, bmhp, obat, administrasi)
- [ ] Can specify amount and percentage for each component
- [ ] Total breakdown is calculated correctly

### Filtering
- [ ] Can filter by service
- [ ] Can filter by service class
- [ ] Can filter by date range
- [ ] Can filter by payer type

## 6. Human Resource Management

### CRUD Operations
- [ ] Can access HR list page
- [ ] Can create new HR record
- [ ] Can edit existing HR record
- [ ] Can view HR details
- [ ] Can delete HR record (only if no active assignments)

### Validation Rules
- [ ] NIP must be unique
- [ ] Name is required
- [ ] Category is required
- [ ] Position is required
- [ ] Employment status is required
- [ ] Inactive HR cannot receive new assignments

### HR Assignments
- [ ] Can view HR assignments
- [ ] Can create new assignment
- [ ] Can edit assignment
- [ ] Can delete assignment
- [ ] Total allocation percentage cannot exceed 100%
- [ ] Inactive assignments don't count toward limit
- [ ] Expired assignments don't count toward limit

### Filtering
- [ ] Can filter by category
- [ ] Can filter by unit (via assignments)
- [ ] Can filter by active status

## 7. Asset Management

### CRUD Operations
- [ ] Can access asset list page
- [ ] Can create new asset
- [ ] Can edit existing asset
- [ ] Can view asset details
- [ ] Can delete asset
- [ ] Can view asset movement history

### Validation Rules
- [ ] Code must be unique
- [ ] Name is required
- [ ] Category is required
- [ ] Acquisition value is required and must be positive
- [ ] Acquisition date is required
- [ ] Useful life years is required for depreciable assets
- [ ] Depreciation method is required for depreciable assets

### Asset Movement
- [ ] Can move asset to different location
- [ ] Movement creates tracking record
- [ ] Movement updates current_location_id
- [ ] Movement history shows chronological order
- [ ] Can move from null location (initial placement)

### Depreciation
- [ ] Straight line depreciation calculates correctly
- [ ] Declining balance depreciation calculates correctly
- [ ] Accumulated depreciation doesn't exceed depreciable value
- [ ] Book value never goes below residual value
- [ ] Can view depreciation schedule

### Filtering
- [ ] Can filter by category
- [ ] Can filter by location
- [ ] Can filter by condition
- [ ] Can filter by active status

## 8. Dashboard

### Summary Statistics
- [ ] Shows total count for each master data category
- [ ] Shows active vs inactive counts
- [ ] Shows recent changes log
- [ ] Shows data quality metrics

### Data Quality Metrics
- [ ] Shows percentage of complete records
- [ ] Shows records with missing optional fields
- [ ] Shows inactive records count
- [ ] Shows records pending approval (if applicable)

## 9. Integration Tests

### API Endpoints
- [ ] Organization units API returns correct data
- [ ] COA API returns correct data
- [ ] Funding sources API filters by date correctly
- [ ] Services API filters by category and unit
- [ ] Tariffs API returns applicable tariff for given parameters
- [ ] HR API filters by unit correctly
- [ ] Assets API filters by location correctly

### Event Dispatching
- [ ] MasterDataCreated event fires on create
- [ ] MasterDataUpdated event fires on update
- [ ] MasterDataDeleted event fires on delete
- [ ] Events contain correct payload (entity_type, entity_id, user_id, timestamp)

### Cross-Entity Relationships
- [ ] Service belongs to organization unit
- [ ] Tariff belongs to service
- [ ] HR assignment belongs to HR and unit
- [ ] Asset belongs to location (organization unit)
- [ ] Asset movement tracks location changes
- [ ] Deleting parent with children is prevented
- [ ] Deleting entity with dependencies is prevented

## 10. Performance

### Page Load Times
- [ ] List pages load within 2 seconds
- [ ] Detail pages load within 1 second
- [ ] Create/edit forms load within 1 second
- [ ] Dashboard loads within 3 seconds

### Query Performance
- [ ] List pages with 1000+ records load efficiently
- [ ] Hierarchy queries use indexed paths
- [ ] Tariff lookup queries are optimized
- [ ] Export operations complete within reasonable time

## 11. Security

### Authentication
- [ ] Unauthenticated users are redirected to login
- [ ] Session timeout works correctly
- [ ] Logout clears session properly

### Authorization
- [ ] Permission checks work on all CRUD operations
- [ ] Users see only allowed menu items
- [ ] Direct URL access is blocked without permission
- [ ] 403 error shown for unauthorized access

### Audit Trail
- [ ] All create operations are logged
- [ ] All update operations are logged
- [ ] All delete operations are logged
- [ ] Logs contain user_id and timestamp
- [ ] Logs contain changed fields for updates

## 12. Error Handling

### User-Friendly Messages
- [ ] Validation errors show clear messages
- [ ] Database errors show generic message (not technical details)
- [ ] Permission errors show appropriate message
- [ ] Not found errors show 404 page

### Edge Cases
- [ ] Empty list pages show appropriate message
- [ ] No search results show appropriate message
- [ ] Invalid input is handled gracefully
- [ ] Concurrent updates are handled correctly

## Test Results Summary

Date: _______________
Tester: _______________

Total Tests: _______________
Passed: _______________
Failed: _______________
Blocked: _______________

Critical Issues Found:
1. _______________
2. _______________
3. _______________

Notes:
_______________________________________________
_______________________________________________
_______________________________________________
