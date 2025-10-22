<div>

    <div class="flex justify-between items-center">
        <flux:heading size="2xl" level="1" >
            {{__('Sync profiles')}}
        </flux:heading>

        <flux:button
            class="text-leev-primary-300 cursor-pointer "
            icon="plus"
            variant="secondary"
            wire:click="openModal('create')"
        >

        </flux:button>

    </div>

    <flux:separator variant="subtle" class="my-6" />

    <div class="bg-white dark:bg-zinc-900 rounded-lg border border-gray-200 dark:border-zinc-700 px-6 p-3">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-700">
                    @foreach($profiles as $key => $profile)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-semibold text-black dark:text-white tracking-wider ">
                                   {{$profile->name}}
                            </span>
                            <br>
                            <span class="text-sm font-thin text-gray-400  dark:text-white/30">{{$profile->description}}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <flux:button
                                icon="pencil"
                                wire:click="openModal('update', {{$key}})"
                                class="cursor-pointer"
                             />
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <flux:modal  class="min-w-2xl" position="top" name="profile-create"  >

        <div class="space-y-6">

            <flux:heading size="xl" level="2">
                Create a new profile
            </flux:heading>


            <flux:separator />

            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input wire:model="name" placeholder="" />
                <flux:error name="name" />
            </flux:field>

            <flux:textarea
                label="Description"
                wire:model="description"
                placeholder="What an incredible description..."
            />

            <flux:button
                wire:click="createProfile"
                variant="secondary"
                class="w-full"

                >
                Create profile
            </flux:button>

        </div>


    </flux:modal>

    <flux:modal  class="min-w-2xl" position="top" name="profile-update"  >

        <div class="space-y-6">

            <flux:heading size="xl" level="2">
                Update : {{$selectedProfile->name ?? ''}}
            </flux:heading>
            <flux:separator />

            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input wire:model="name" placeholder="" />
                <flux:error name="name" />
            </flux:field>

            <flux:textarea
                label="Description"
                wire:model="description"
                placeholder="What an incredible description..."
            />

            <div class="grid grid-cols-5 gap-4">
                <flux:button
                    wire:click="updateProfile"
                    variant="primary"
                    class="w-full col-span-4"
                >
                    Update profile
                </flux:button>

                <flux:button
                    wire:click="deleteProfile"
                    variant="danger"
                    icon="trash"
                    class="w-full"

                />

            </div>


        </div>


    </flux:modal>


</div>
