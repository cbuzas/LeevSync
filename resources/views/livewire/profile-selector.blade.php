<div>

    <flux:select class="mt-1" wire:model="currentProfile" wire:change="changeCurrentProfile()" placeholder="Choose profile...">

        @foreach($profiles as $profile)
                <flux:select.option value="{{ $profile['id'] }}">
                    {{ $profile['name'] }}
                </flux:select.option>
            @endforeach

    </flux:select>

</div>
