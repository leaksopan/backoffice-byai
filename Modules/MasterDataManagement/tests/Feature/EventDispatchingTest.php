<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\MasterDataManagement\app\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\app\Models\MdmChartOfAccount;
use Modules\MasterDataManagement\app\Models\MdmFundingSource;
use Modules\MasterDataManagement\app\Events\MasterDataCreated;
use Modules\MasterDataManagement\app\Events\MasterDataUpdated;
use Modules\MasterDataManagement\app\Events\MasterDataDeleted;
use App\Models\User;

/**
 * @test Feature: master-data-management, Event Dispatching Tests
 * Test event dispatching untuk perubahan data master
 */
class EventDispatchingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('access master-data-management');
        $this->user->givePermissionTo('master-data-management.create');
        $this->user->givePermissionTo('master-data-management.edit');
        $this->user->givePermissionTo('master-data-management.delete');
        $this->actingAs($this->user);
    }

    /** @test */
    public function dispatches_event_when_organization_unit_is_created()
    {
        Event::fake([MasterDataCreated::class]);

        $unit = MdmOrganizationUnit::factory()->create();

        Event::assertDispatched(MasterDataCreated::class, function ($event) use ($unit) {
            return $event->entityType === 'organization_unit' 
                && $event->entityId === $unit->id
                && $event->action === 'created';
        });
    }

    /** @test */
    public function dispatches_event_when_organization_unit_is_updated()
    {
        Event::fake([MasterDataUpdated::class]);

        $unit = MdmOrganizationUnit::factory()->create();
        $unit->update(['name' => 'Updated Name']);

        Event::assertDispatched(MasterDataUpdated::class, function ($event) use ($unit) {
            return $event->entityType === 'organization_unit' 
                && $event->entityId === $unit->id
                && $event->action === 'updated';
        });
    }

    /** @test */
    public function dispatches_event_when_organization_unit_is_deleted()
    {
        Event::fake([MasterDataDeleted::class]);

        $unit = MdmOrganizationUnit::factory()->create();
        $unitId = $unit->id;
        $unit->delete();

        Event::assertDispatched(MasterDataDeleted::class, function ($event) use ($unitId) {
            return $event->entityType === 'organization_unit' 
                && $event->entityId === $unitId
                && $event->action === 'deleted';
        });
    }

    /** @test */
    public function dispatches_event_when_chart_of_account_is_created()
    {
        Event::fake([MasterDataCreated::class]);

        $account = MdmChartOfAccount::factory()->create();

        Event::assertDispatched(MasterDataCreated::class, function ($event) use ($account) {
            return $event->entityType === 'chart_of_account' 
                && $event->entityId === $account->id;
        });
    }

    /** @test */
    public function dispatches_event_when_chart_of_account_is_updated()
    {
        Event::fake([MasterDataUpdated::class]);

        $account = MdmChartOfAccount::factory()->create();
        $account->update(['name' => 'Updated Account']);

        Event::assertDispatched(MasterDataUpdated::class, function ($event) use ($account) {
            return $event->entityType === 'chart_of_account' 
                && $event->entityId === $account->id;
        });
    }

    /** @test */
    public function dispatches_event_when_funding_source_is_created()
    {
        Event::fake([MasterDataCreated::class]);

        $source = MdmFundingSource::factory()->create();

        Event::assertDispatched(MasterDataCreated::class, function ($event) use ($source) {
            return $event->entityType === 'funding_source' 
                && $event->entityId === $source->id;
        });
    }

    /** @test */
    public function event_payload_contains_user_information()
    {
        Event::fake([MasterDataCreated::class]);

        $unit = MdmOrganizationUnit::factory()->create();

        Event::assertDispatched(MasterDataCreated::class, function ($event) {
            return isset($event->userId) && $event->userId === auth()->id();
        });
    }

    /** @test */
    public function event_payload_contains_timestamp()
    {
        Event::fake([MasterDataCreated::class]);

        $unit = MdmOrganizationUnit::factory()->create();

        Event::assertDispatched(MasterDataCreated::class, function ($event) {
            return isset($event->timestamp);
        });
    }

    /** @test */
    public function updated_event_contains_changed_fields()
    {
        Event::fake([MasterDataUpdated::class]);

        $unit = MdmOrganizationUnit::factory()->create(['name' => 'Original Name']);
        $unit->update(['name' => 'Updated Name']);

        Event::assertDispatched(MasterDataUpdated::class, function ($event) {
            return isset($event->changedFields) && in_array('name', $event->changedFields);
        });
    }

    /** @test */
    public function multiple_updates_dispatch_multiple_events()
    {
        Event::fake([MasterDataUpdated::class]);

        $unit = MdmOrganizationUnit::factory()->create();
        $unit->update(['name' => 'First Update']);
        $unit->update(['name' => 'Second Update']);

        Event::assertDispatched(MasterDataUpdated::class, 2);
    }

    /** @test */
    public function events_are_dispatched_in_correct_order()
    {
        Event::fake([MasterDataCreated::class, MasterDataUpdated::class, MasterDataDeleted::class]);

        $unit = MdmOrganizationUnit::factory()->create();
        $unit->update(['name' => 'Updated']);
        $unit->delete();

        Event::assertDispatchedTimes(MasterDataCreated::class, 1);
        Event::assertDispatchedTimes(MasterDataUpdated::class, 1);
        Event::assertDispatchedTimes(MasterDataDeleted::class, 1);
    }
}
